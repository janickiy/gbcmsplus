<?php

namespace mcms\statistic\components;

use mcms\common\mgmp\MgmpClient;
use mcms\payments\Module;
use mcms\promo\models\Country;
use mcms\statistic\models\mysql\MainAdminStatistic;
use rgk\utils\interfaces\ExecutableInterface;
use Yii;
use yii\base\Exception;
use yii\base\InvalidParamException;
use mcms\statistic\components\mainStat\BaseFetch;
use mcms\statistic\components\mainStat\FormModel;
use mcms\statistic\components\mainStat\Group;
use mcms\statistic\components\mainStat\mysql\Row;
use yii\base\Object;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use mcms\statistic\models\ResellerHoldRule;
use mcms\statistic\components\ResellerUnholdRuleHash as Hasher;
use yii\web\NotFoundHttpException;

class ResellerProfits extends Object implements ExecutableInterface
{
  public $dateFrom;
  public $dateTo;
  /**
   * @var bool По-умолчанию скрипт обновит только те холды, у стран которых изменилось правило расхолда.
   * Но если данную настройку сделать true, то обновятся независимо от того именилось правило расхолда или нет.
   * Обычно это необходимо если в расчете изменилась какая-то логика и надо пересчитать на проде.
   */
  public $forceUpdateHolds = false;
  /** @var callable */
  public $logger;

  /** @var  ResellerHoldRule[] */
  protected $countriesRules;
  protected $resellerId;

  /** @var  string Дата, с которой расчитывается взаимодействие mcms <-> mgmp */
  protected $financeStart;

  /**
   * Таблица с профитами
   * @return string
   */
  public static function tableName()
  {
    return 'reseller_profits';
  }

  /**
   * @return bool
   * @throws \yii\base\Exception
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\db\Exception
   * @throws \yii\web\NotFoundHttpException
   * @throws \yii\base\InvalidParamException
   */
  public function execute()
  {
    if (!$this->logger) throw new InvalidParamException('ResellerProfits::logger обязателен');

    /** @var \mcms\payments\Module $modulePayments */
    $modulePayments = Yii::$app->getModule('payments');

    $this->financeStart = $modulePayments->getLeftBorderDate();

    if (!$this->financeStart) {
      $this->log('LEFT BORDER DATE IS NOT SPECIFIED. SCRIPT STOPPED');
      return;
    }

    $this->countriesRules = ResellerHoldRule::getCountriesRules();

    /** @var \mcms\user\Module $moduleUsers */
    $moduleUsers = Yii::$app->getModule('users');

    $reseller = $moduleUsers->api('usersByRoles', ['reseller'])->getResult();

    if (!$reseller) throw new NotFoundHttpException('Reseller не найден');
    $this->resellerId = current($reseller)['id'];

    $this->log('Checking updated rules, actualize unhold_date');

    $this->actualizeProfits();

    $dateFrom = $this->dateFrom ?: Yii::$app->formatter->asDate("- 1 days", 'php:Y-m-d');
    $dateTo = $this->dateTo ?: Yii::$app->formatter->asDate('today', 'php:Y-m-d');
    for (
      $date = $dateFrom;
      $date <= $dateTo;
      $date = Yii::$app->formatter->asDate("$date + 1 day", 'php:Y-m-d')) {
      $this->log($date);
      $unholds = $this->getCountriesUnholds($date);

      $this->log('-- delete day profits ... ', false);

      $this->deleteDayProfits($date);

      $this->log('done');

      $newProfits = $date >= $this->financeStart;
      $this->log('-- profits are ' . ($newProfits ? 'new' : 'old'));
      $this->log('-- reseller profits ... ', false);
      $counter = 0;

      foreach ($this->getResellerProfits($date) as $countryId => $resellerProfit) {
        $unhold = $unholds[$countryId];

        if (!$unhold) {
          continue;
        }

        $counter += $this->insertProfit([
          'country_id' => $countryId,
          'date' => $date,
          'week_start' => new Expression('date - INTERVAL WEEKDAY(date) DAY'),
          'month_start' => new Expression('DATE_FORMAT(date, \'%Y-%m-01\')'),

          'profit_rub' => ArrayHelper::getValue($resellerProfit, 'rub.total', 0),
          'profit_revshare_rub' => ArrayHelper::getValue($resellerProfit, 'rub.revshare', 0),
          'profit_cpa_sold_rub' => ArrayHelper::getValue($resellerProfit, 'rub.cpa_sold', 0),
          'profit_cpa_rejected_rub' => ArrayHelper::getValue($resellerProfit, 'rub.cpa_rejected', 0),
          'profit_onetime_rub' => ArrayHelper::getValue($resellerProfit, 'rub.onetime', 0),

          'profit_usd' => ArrayHelper::getValue($resellerProfit, 'usd.total', 0),
          'profit_revshare_usd' => ArrayHelper::getValue($resellerProfit, 'usd.revshare', 0),
          'profit_cpa_sold_usd' => ArrayHelper::getValue($resellerProfit, 'usd.cpa_sold', 0),
          'profit_cpa_rejected_usd' => ArrayHelper::getValue($resellerProfit, 'usd.cpa_rejected', 0),
          'profit_onetime_usd' => ArrayHelper::getValue($resellerProfit, 'usd.onetime', 0),

          'profit_eur' => ArrayHelper::getValue($resellerProfit, 'eur.total', 0),
          'profit_revshare_eur' => ArrayHelper::getValue($resellerProfit, 'eur.revshare', 0),
          'profit_cpa_sold_eur' => ArrayHelper::getValue($resellerProfit, 'eur.cpa_sold', 0),
          'profit_cpa_rejected_eur' => ArrayHelper::getValue($resellerProfit, 'eur.cpa_rejected', 0),
          'profit_onetime_eur' => ArrayHelper::getValue($resellerProfit, 'eur.onetime', 0),

          // TRICKY расхолд считаем только с момента подключения к finance
          'unhold_date' => $newProfits ? $unhold->unholdDate : $date,
          'unhold_week_start' => $newProfits
            ? new Expression('unhold_date - INTERVAL WEEKDAY(unhold_date) DAY')
            : new Expression('date - INTERVAL WEEKDAY(date) DAY'),
          'unhold_month_start' => $newProfits
            ? new Expression("DATE_FORMAT(unhold_date, '%Y-%m-01')")
            : new Expression("DATE_FORMAT(date, '%Y-%m-01')"),

          'description' => Json::encode(ArrayHelper::toArray($unhold)),
          'rule_hash' => (new Hasher(['holdRule' => $unhold->holdRule]))->getHash(),
          'created_at' => new Expression('UNIX_TIMESTAMP()'),
        ]);
      }

      $this->log("$counter rows affected");
    }

    $this->log('SUCCESS');
  }

  /**
   * @param $date
   * @return Query
   */
  protected function maxUnholdDateQuery($date)
  {
    return (new Query())
      ->select([
        'country_id',
        'maxUnholdDate' => new Expression('MAX(date)')
      ])
      ->from(static::tableName())
      ->andWhere(['<=', 'unhold_date', $date])
      ->andWhere(['<', 'date', $date]);
  }

  /**
   * @param $date
   * @return Query
   */
  protected function minHoldDateQuery($date)
  {
    return (new Query())
      ->select([
        'country_id',
        'minHoldDate' => new Expression('MIN(date)')
      ])
      ->from(static::tableName())
      ->andWhere(['>', 'unhold_date', $date])
      ->andWhere(['<', 'date', $date]);
  }

  /**
   * @param $date
   * @return ResellerUnholdDateCalc[]
   * @throws Exception
   */
  protected function getCountriesUnholds($date)
  {
    $listMaxUnholdDate = $this->maxUnholdDateQuery($date)
      ->groupBy('country_id')
      ->indexBy('country_id')
      ->all();

    $listMinHoldDate = $this->minHoldDateQuery($date)
      ->groupBy('country_id')
      ->indexBy('country_id')
      ->all();

    $merged = ArrayHelper::merge($listMaxUnholdDate, $listMinHoldDate);

    $countriesUnholds = [];

    foreach (Country::find()->all() as $country) {
      /** @var Country $country */
      if (!$rule = ArrayHelper::getValue($this->countriesRules, $country->id)) {
        $countriesUnholds[$country->id] = null;
        continue;
      }

      /** @var Country $country */
      $countryUnholds = ArrayHelper::getValue($merged, $country->id);
      $calc = new ResellerUnholdDateCalc([
        'holdRule' => $rule,
        'holdDate' => $date,
        'maxUnholdDate' => ArrayHelper::getValue($countryUnholds, 'maxUnholdDate'),
        'minHoldDate' => ArrayHelper::getValue($countryUnholds, 'minHoldDate'),
      ]);
      $calc->getUnholdDate();
      $countriesUnholds[$country->id] = $calc;
    }

    return $countriesUnholds;
  }

  /**
   * @param $date
   * @return array
   */
  private function getResellerProfits($date)
  {
    $data = [];

    foreach (['rub', 'usd', 'eur'] as $currency) {
      /* @var BaseFetch $fetch */
      $fetch = $this->statFetcher($currency, $date);

      foreach ($fetch->getDataProvider()->getModels() as $row) {
        /** @var $row Row */
        $profit = $row->getResellerTurnover();
        if (!$profit['total']) continue;

        if (empty($data[$row->getGroup()])) {
          $data[$row->getGroup()] = [];
        }

        $data[$row->getGroup()][$currency] = $profit;
      }

    }

    return $data;
  }

  /**
   * @param null $currency
   * @param $date
   * @return BaseFetch
   */
  private function statFetcher($currency = null, $date)
  {
    $formModel = new FormModel([
      'dateFrom' => $date,
      'dateTo' => $date,
      'groups' => [Group::BY_COUNTRIES],
      'currency' => $currency,
    ]);

    return Yii::$container->get(BaseFetch::class, [$formModel]);
  }

  /**
   * проверить существующие профиты, которые не расхолдились и если изменился хэш,
   * то пересчитать unhold_date для этих профитов
   * @throws \yii\db\Exception
   */
  private function actualizeProfits()
  {
    foreach ($this->countriesRules as $countryId => $rule) {

      if (!$rule) {
        $this->log("-- no unhold rule for country_id=$countryId");
        continue;
      }

      $this->log("-- actualize country_id=$countryId ... ", false);

      $actualHash = (new Hasher(['holdRule' => $rule]))->getHash();

      $recalc = (new Query())
        ->select('*')
        ->from(static::tableName())
        ->andWhere(['>', 'unhold_date', new Expression('CURRENT_DATE()')])
        ->andWhere(['country_id' => $countryId])
        ->orderBy('date');

      if (!$this->forceUpdateHolds) {
        $recalc->andWhere(['<>', 'rule_hash', $actualHash]);
      }

      $counter = 0;
      $mgmpRecalcDateFrom = null;
      foreach ($recalc->each() as $row) {
        if (!$mgmpRecalcDateFrom || strtotime($row['date']) < strtotime($mgmpRecalcDateFrom)) {
          $mgmpRecalcDateFrom = $row['date'];
        }

        $maxUnholdDateQuery = $this->maxUnholdDateQuery($row['date'])
          ->andWhere(['country_id' => $countryId])
          ->one();

        $minHoldDateQuery = $this->minHoldDateQuery($row['date'])
          ->andWhere(['country_id' => $countryId])
          ->one();

        $calc = new ResellerUnholdDateCalc([
          'holdRule' => $rule,
          'holdDate' => $row['date'],
          'maxUnholdDate' => ArrayHelper::getValue($maxUnholdDateQuery, 'maxUnholdDate'),
          'minHoldDate' => ArrayHelper::getValue($minHoldDateQuery, 'maxUnholdDate')
        ]);

        $counter += Yii::$app->db->createCommand()->update(static::tableName(), [
          'unhold_date' => $calc->getUnholdDate(),
          'unhold_week_start' => new Expression('unhold_date - INTERVAL WEEKDAY(unhold_date) DAY'),
          'unhold_month_start' => new Expression("DATE_FORMAT(unhold_date, '%Y-%m-01')"),
          'rule_hash' => $actualHash,
          'description' => Json::encode(ArrayHelper::toArray($calc)),
          'updated_at' => time()
        ], ['id' => $row['id']])->execute();
      }
      $this->requestMgmpProfitRecalc($mgmpRecalcDateFrom, $countryId);

      $this->log("$counter rows affected");
    }
    $this->log('-- all countries actualized');
  }

  /**
   * @param $data
   * @return int
   * @throws \yii\db\Exception
   */
  protected function insertProfit($data)
  {
    $sql = Yii::$app->db->createCommand()->insert(static::tableName(), [
      'country_id' => $data['country_id'],
      'date' => $data['date'],
      'week_start' => $data['week_start'],
      'month_start' => $data['month_start'],

      'profit_rub' => $data['profit_rub'],
      'profit_revshare_rub' => $data['profit_revshare_rub'],
      'profit_cpa_sold_rub' => $data['profit_cpa_sold_rub'],
      'profit_cpa_rejected_rub' => $data['profit_cpa_rejected_rub'],
      'profit_onetime_rub' => $data['profit_onetime_rub'],

      'profit_usd' => $data['profit_usd'],
      'profit_revshare_usd' => $data['profit_revshare_usd'],
      'profit_cpa_sold_usd' => $data['profit_cpa_sold_usd'],
      'profit_cpa_rejected_usd' => $data['profit_cpa_rejected_usd'],
      'profit_onetime_usd' => $data['profit_onetime_usd'],

      'profit_eur' => $data['profit_eur'],
      'profit_revshare_eur' => $data['profit_revshare_eur'],
      'profit_cpa_sold_eur' => $data['profit_cpa_sold_eur'],
      'profit_cpa_rejected_eur' => $data['profit_cpa_rejected_eur'],
      'profit_onetime_eur' => $data['profit_onetime_eur'],

      'unhold_date' => $data['unhold_date'],
      'unhold_week_start' => $data['unhold_week_start'],
      'unhold_month_start' => $data['unhold_month_start'],
      'description' => $data['description'],
      'rule_hash' => $data['rule_hash'],
      'created_at' => $data['created_at'],
    ]);

    return Yii::$app->db
      ->createCommand($sql->rawSql . ' ON DUPLICATE KEY UPDATE 
          updated_at = UNIX_TIMESTAMP(),
          
          profit_rub = VALUES(profit_rub),          
          profit_revshare_rub = VALUES(profit_revshare_rub),
          profit_cpa_sold_rub = VALUES(profit_cpa_sold_rub),
          profit_cpa_rejected_rub = VALUES(profit_cpa_rejected_rub),
          profit_onetime_rub = VALUES(profit_onetime_rub),

          profit_usd = VALUES(profit_usd),
          profit_revshare_usd = VALUES(profit_revshare_usd),
          profit_cpa_sold_usd = VALUES(profit_cpa_sold_usd),
          profit_cpa_rejected_usd = VALUES(profit_cpa_rejected_usd),
          profit_onetime_usd = VALUES(profit_onetime_usd),

          profit_eur = VALUES(profit_eur),
          profit_revshare_eur = VALUES(profit_revshare_eur),
          profit_cpa_sold_eur = VALUES(profit_cpa_sold_eur),
          profit_cpa_rejected_eur = VALUES(profit_cpa_rejected_eur),
          profit_onetime_eur = VALUES(profit_onetime_eur),
          
          unhold_date = VALUES(unhold_date),
          unhold_week_start = VALUES(unhold_week_start),
          unhold_month_start = VALUES(unhold_month_start),
          description = VALUES(description),
          rule_hash = VALUES(rule_hash)
          ')
      ->execute();
  }

  /**
   * Когда подписка выкупается, профит переходит с реселлера к инвестору грубо говоря.
   * То есть в профите реса надо "обнулить" соответствующую строку по соответствующей стране.
   * И у нас при INSERT ON DUPLICATE KEY UPDATE не всегда переобновляет такие профиты для реса,
   * т.к. по этой стране например больше профитов нет.
   * Поэтому решено перед записью новых профитов за день, удалить из БД то что есть сейчас за этот день.
   * Тогда мы будем иметь самые актуальные профиты.
   *
   * @param $date
   */
  private function deleteDayProfits($date)
  {
    Yii::$app->db->createCommand()->delete(static::tableName(), ['date' => $date])->execute();
  }

  /**
   * @param $recalcDateFrom
   * @param $countryId
   */
  private function requestMgmpProfitRecalc($recalcDateFrom, $countryId)
  {
    /** @var Module $paymentsModule */
    $paymentsModule = Yii::$app->getModule('payments');
    Yii::$app->mgmpClient->requestData(MgmpClient::URL_RESELLER_PROFIT_IMPORT, [
      'resellerId' => $paymentsModule->getMgmpResellerId(),
      'dateFrom' => $recalcDateFrom,
      'countryId' => $countryId,
    ]);
  }

  /**
   * @param $message
   * @param bool $breakAfter
   * @param bool $breakBefore
   */
  private function log($message, $breakAfter = true, $breakBefore = false)
  {
    call_user_func($this->logger, $message, $breakAfter, $breakBefore);
  }
}