<?php

namespace mcms\payments\components;

use mcms\common\traits\Translate;
use mcms\payments\components\exceptions\UserBalanceException;
use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserBalancesGroupedByDay;
use mcms\payments\models\UserPaymentSetting;
use mcms\promo\Module;
use mcms\statistic\models\resellerStatistic\ItemSearch;
use mcms\user\models\User;
use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Class UserBalance
 * @package mcms\payments\components
 *
 * @property double $main
 * @property double $toPayment
 * @property double $paymentsMonthly
 * @property double $paymentsWeekly
 * @property double $paymentsTotal
 */
class UserBalance extends Model
{
  use Translate;
  const LANG_PREFIX = 'payments.user-balance.';

  const BALANCE_MAIN = 'main';
  const BALANCE_HOLD = 'hold';

  const CACHE_KEY_PREFIX = 'user_balances__';
  const CACHE_DURATION = 60;

  public $userId;
  /** @var string|null TRICKY Валюта может быть пуста */
  public $currency;

  private $balance;
  /** @var Module $promoModule */
  private $promoModule;

  private $currencyLabel;
  private $paymentSettings;

  public $showLog = true;

  private $_groupedBalance;

  /**
   * @inheritDoc
   */
  public function __construct($config = [])
  {
    if (($this->userId = ArrayHelper::getValue($config, 'userId')) === null) {
      throw new UserBalanceException('userId required');
    }
    parent::__construct($config);
  }

  /**
   * @inheritDoc
   */
  public function init()
  {
    $this->promoModule = Yii::$app->getModule('promo');

    // для реселлера можно задать валюту
    $this->currency =
      $this->currency ?: $this->getPaymentSettings()->getCurrentCurrency();

    $this->currencyLabel = ArrayHelper::getValue($this->getPaymentSettings()->getCurrencyList(), $this->currency);

    parent::init();
  }

  private function getCacheKey()
  {
    return self::CACHE_KEY_PREFIX . $this->currency . $this->userId;
  }

  /**
   * Получить балланс по типу
   * @param $type
   * @param bool $useCache
   * @return float
   */
  private function getBalanceByType($type, $useCache = true, $dateFrom = null, $dateTo = null)
  {
    $this->balance = $useCache
      ? (
      $this->balance === null
        ? Yii::$app->cache->get($this->getCacheKey())
        : $this->balance
      )
      : null;

    if (($result = ArrayHelper::getValue($this->balance, [$this->currency, $type])) !== null) {
      return $result;
    }

    $userProfit = UserBalancesGroupedByDay::getProfit($this->userId, $this->currency, $dateFrom, $dateTo);
    $userInvoice = UserBalanceInvoice::getInvoice($this->userId, $this->currency, $dateFrom, $dateTo);

    $this->balance[$this->currency] = [
      self::BALANCE_MAIN => round(
        ArrayHelper::getValue($userProfit, UserBalancesGroupedByDay::UNHOLD_BALANCE) +
        ArrayHelper::getValue($userInvoice, UserBalanceInvoice::UNHOLD_INVOICES),
        3
      ),
      self::BALANCE_HOLD => round(
        ArrayHelper::getValue($userProfit, UserBalancesGroupedByDay::HOLD_BALANCE) +
        ArrayHelper::getValue($userInvoice, UserBalanceInvoice::HOLD_INVOICES),
        3
      ),
    ];

    $useCache && Yii::$app->cache->set($this->getCacheKey(), $this->balance, self::CACHE_DURATION);
    return (float)ArrayHelper::getValue($this->balance, [$this->currency, $type]);
  }

  public function invalidateCache()
  {
    return Yii::$app->cache->delete($this->getCacheKey());
  }

  /**
   * Возвращает профиты пользователя, сгруппированные по дате и стране
   * Профиты не находящиеся в холде, имеют текущую дату и страну = 0
   * Используется при конвертации, для создания соответствующих инвойсов
   *
   * Возвращает массив вида $result[country_id][date] = balance
   *
   * @return array
   */
  public function getGroupedBalance()
  {
    if ($this->_groupedBalance) return $this->_groupedBalance;
    $userProfit = UserBalancesGroupedByDay::getHoldProfit($this->userId, $this->currency);
    $userInvoice = UserBalanceInvoice::getHoldInvoices($this->userId, $this->currency);

    $this->_groupedBalance = [];
    // Доходы
    foreach ($userProfit as $item) {
      $this->_groupedBalance[$item['country_id']][$item['date']] = $item['amount'];
    }
    // Инвойсы
    foreach ($userInvoice as $item) {
      if (empty($this->_groupedBalance[$item['country_id']])) {
        $this->_groupedBalance[$item['country_id']] = [];
      }
      if (empty($this->_groupedBalance[$item['country_id']][$item['date']])) {
        $this->_groupedBalance[$item['country_id']][$item['date']] = 0;
      }
      $this->_groupedBalance[$item['country_id']][$item['date']] += $item['amount'];
    }
    // Расхолдированный профит
    $this->_groupedBalance[0][date('Y-m-d')] = $this->getMain(false);

    return  $this->_groupedBalance;
  }

  /**
   * Общий балланс (с учетом холдов)
   * @param bool $useCache
   * @return float
   */
  public function getBalance($useCache = true)
  {
    return round(
      $this->getBalanceByType(self::BALANCE_MAIN, $useCache) +
      $this->getBalanceByType(self::BALANCE_HOLD, $useCache),
      3
    );
  }

  /**
   * Балланс без учета холдов
   * @param bool $useCache
   * @param null $dateFrom
   * @param null $dateTo
   * @return float
   */
  public function getMain($useCache = true, $dateFrom = null, $dateTo = null)
  {
    return $this->getBalanceByType(self::BALANCE_MAIN, $useCache, $dateFrom, $dateTo);
  }

  /**
   * Балланс за сегодня
   * @return float
   */
  public function getTodayProfit()
  {
    return UserBalancesGroupedByDay::getTodayProfit($this->userId, $this->currency);
  }

  /**
   * Балланс в холде
   * @param bool $useCache
   * @return float
   */
  public function getHold($useCache = true)
  {
    return $this->getBalanceByType(self::BALANCE_HOLD, $useCache);
  }

  public function getCurrencyLabel()
  {
    return $this->currencyLabel;
  }

  public function getCurrency()
  {
    return $this->currency;
  }

  public static function getCurrencies()
  {
    return Yii::$app->getModule('promo')->api('mainCurrencies')->setMapParams(['code', 'name'])->getMap();
  }


  /**
   * @inheritDoc
   */
  public function attributeLabels()
  {
    return [
      'resellerBalance' => self::translate('attribute-main'),
      'main' => self::translate('attribute-main'),
    ];
  }

  /**
   * @return UserPaymentSetting
   */
  public function getPaymentSettings()
  {
    if ($this->paymentSettings !== null) {
      return $this->paymentSettings;
    }
    return $this->paymentSettings = UserPaymentSetting::fetch($this->userId);
  }

  /**
   * @param mixed $currency
   * @return UserBalance
   */
  public function setCurrency($currency)
  {
    $this->currency = $currency;
    return $this;
  }

  /**
   * Сумма балансов всех пользователей
   * @return array
   */
  public static function getBallancesGroupedByCurrency()
  {
    // TRICKY При кэшировании надо привязываться к текущему пользователю, если что ))
    /** @var \mcms\user\Module $userModule */
    $userModule = Yii::$app->getModule('users');
    $subQuery = (new Query())
      ->select('u.id')
      ->from(User::tableName() . ' u')
      ->leftJoin(UserPaymentSetting::tableName() . ' ups', 'u.id=ups.user_id')
      ->innerJoin('auth_assignment aa', 'u.id = aa.user_id AND aa.item_name <> :item_name')
      ->addParams([':item_name' => $userModule::PARTNER_ROLE]);

    $balances = (new Query())
      ->from(UserBalancesGroupedByDay::tableName() . ' ubgd')
      ->select([
        'sum(if(ubgd.user_currency = "rub", ubgd.profit_rub, 0)) AS rub',
        'sum(if(ubgd.user_currency = "usd", ubgd.profit_usd, 0)) AS usd',
        'sum(if(ubgd.user_currency = "eur", ubgd.profit_eur, 0)) AS eur',
      ])
      ->innerJoin(User::tableName() . ' u', 'u.id=ubgd.user_id')
      ->andWhere(['u.status' => User::STATUS_ACTIVE])
      ->andWhere(['NOT IN', 'user_id', $subQuery])->one();

    $invoices = ArrayHelper::map(UserBalanceInvoice::find()
      ->select([
        'SUM(amount) as amount',
        'currency',
      ])
      ->innerJoin(
        User::tableName(),
        UserBalanceInvoice::tableName() . '.user_id = ' . User::tableName() . '.id'
      )
      ->innerJoin(
        'auth_assignment aa',
        UserBalanceInvoice::tableName() . '.user_id = aa.user_id AND aa.item_name = :item_name'
      )
      ->addParams([':item_name' => $userModule::PARTNER_ROLE])
      ->andWhere([User::tableName() . '.status' => User::STATUS_ACTIVE])
      ->groupBy('currency')->all(), 'currency', 'amount');


    return [
      'rub' => ArrayHelper::getValue($balances, 'rub') + ArrayHelper::getValue($invoices, 'rub'),
      'usd' => ArrayHelper::getValue($balances, 'usd') + ArrayHelper::getValue($invoices, 'usd'),
      'eur' => ArrayHelper::getValue($balances, 'eur') + ArrayHelper::getValue($invoices, 'eur'),
    ];
  }

  /**
   * Баланс реселлера
   * @return float
   */
  public function getResellerBalance()
  {
    // TODO Пока неизвестно нужно ли учитывать левую границу расчета, так как если нужно, то придется учитывать это везде
    // левая граница расчета реселлера из настроек модуля Payments
    // $dateFrom = Yii::$app->getModule('payments')->getLeftBorderDate();

    $searchModel = (new ItemSearch);
    $models = $searchModel->search([])->getModels();
    $item = reset($models);
    $debtValues = $item ? $item->debt : $searchModel->getResultValue('debt');

    return round($debtValues->getValue($this->currency), 3);
  }

}