<?php

namespace mcms\statistic\components;


use mcms\common\helpers\ArrayHelper;
use mcms\statistic\models\DashboardProfitsOns;
use mcms\statistic\models\DashboardLanding;
use Yii;
use yii\base\Exception;
use yii\base\Object;
use yii\db\Expression;
use yii\db\Query;

/**
 * Компонент для получения данных для дашборда
 * @package mcms\statistic\components
 *
 * @property array $landingsTop
 */
class DashboardData extends Object
{
  public $startDate;
  public $endDate;
  public $countries = [];
  public $operators = [];
  public $users;

  private $_landings = [];
  private $_activePartners = [];
  private $_groupStat = [];

  const RUB = 'rub';
  const USD = 'usd';
  const EUR = 'eur';

  /**
   * @inheritdoc
   */
  public function init()
  {
    parent::init();
    if (!$this->startDate) throw new Exception('Начальная дата не задана');
    if (!$this->endDate) $this->endDate = date('Y-m-d');
  }

  /**
   * Получение топа лендингов
   * @return array
   */
  public function getLandings()
  {
    $hashKey = $this->getHashKey($this->startDate, $this->endDate, $this->countries, $this->operators);
    if (isset($this->_landings[$hashKey])) return $this->_landings[$hashKey];
    $data = DashboardLanding::findAll($this->startDate, $this->endDate, $this->countries, $this->operators, $this->users);
    $this->_landings[$hashKey] = [];
    foreach ($data as $value) {
      $dashboardLanding = new DashboardLanding($value);
      $this->_landings[$hashKey][] = [
        'landing_id' => $dashboardLanding->landing_id,
        'clicks' => $dashboardLanding->clicks,
        'ratio' => $dashboardLanding->ratio,
        'name' => $dashboardLanding->name,
      ];
    }
    return $this->_landings[$hashKey];
  }

  /**
   * Статистика по датам
   * @return array
   */
  public function getStat($groupBy)
  {
    $hashKey = $this->getHashKey($this->startDate, $this->endDate, $this->countries, $this->operators, $groupBy);
    if (isset($this->_groupStat[$hashKey])) return $this->_groupStat[$hashKey];

    $data = DashboardProfitsOns::findAll(
      $groupBy, $this->startDate, $this->endDate, $this->countries, $this->operators, $this->users
    );

    $this->_groupStat[$hashKey] = [];
    foreach ($data as $value) {
      $group = ArrayHelper::getValue($value, $groupBy);
      $dashboardProfitsOns = new DashboardProfitsOns($value);

      $this->_groupStat[$hashKey][$group] = $this->getColumns($dashboardProfitsOns);
    }
    return $this->_groupStat[$hashKey];
  }

  /**
   * Количество активных партнеров по датам
   * @return array
   */
  public function getActivePartners()
  {
    $hashKey = $this->getHashKey($this->startDate, $this->endDate, $this->countries, $this->operators);
    if (isset($this->_activePartners[$hashKey])) return $this->_activePartners[$hashKey];

    $activePartners = DashboardProfitsOns::findActivePartnersCount(
      $this->startDate, $this->endDate, $this->countries, $this->operators, $this->users
    );

    $this->_activePartners[$hashKey] = [];
    foreach ($activePartners as $value) {
      $date = ArrayHelper::getValue($value, 'date');
      $this->_activePartners[$hashKey][$date] = ArrayHelper::getValue($value, 'count');
    }
    return $this->_activePartners[$hashKey];
  }

  /**
   * @return string
   */
  protected function getHashKey()
  {
    $arg_list = func_get_args();
    $result = '';
    foreach ($arg_list as $arg) {
      $result .= '-' . (is_array($arg) ? implode(',', $arg) : $arg);
    }
    return md5($result);
  }

  /**
   * Колонки для запроса
   * @param DashboardProfitsOns $model
   * @return array
   */
  private function getColumns(DashboardProfitsOns $model)
  {
    return [
      'res_gross_revenue_rub' => $model->getResGrossRevenue(self::RUB),
      'res_gross_revenue_usd' => $model->getResGrossRevenue(self::USD),
      'res_gross_revenue_eur' => $model->getResGrossRevenue(self::EUR),
      'partner_gross_revenue_rub' => $model->getPartnerGrossRevenue(self::RUB),
      'partner_gross_revenue_usd' => $model->getPartnerGrossRevenue(self::USD),
      'partner_gross_revenue_eur' => $model->getPartnerGrossRevenue(self::EUR),
      'res_net_revenue_rub' => $model->getResNetRevenue(self::RUB),
      'res_net_revenue_usd' => $model->getResNetRevenue(self::USD),
      'res_net_revenue_eur' => $model->getResNetRevenue(self::EUR),
      'res_cpa_profit_rub' => $model->getResCpaProfit(self::RUB),
      'res_cpa_profit_usd' => $model->getResCpaProfit(self::USD),
      'res_cpa_profit_eur' => $model->getResCpaProfit(self::EUR),
      'partner_cpa_profit_rub' => $model->getPartnerCpaProfit(self::RUB),
      'partner_cpa_profit_usd' => $model->getPartnerCpaProfit(self::USD),
      'partner_cpa_profit_eur' => $model->getPartnerCpaProfit(self::EUR),
      'res_profit_rub' => $model->res_revshare_profit_rub,                 //
      'res_profit_usd' => $model->res_revshare_profit_usd,                 //  RS прибыль
      'res_profit_eur' => $model->res_revshare_profit_eur,                 //
      'partner_profit_rub' => $model->partner_revshare_profit_rub,         //
      'partner_profit_usd' => $model->partner_revshare_profit_usd,         //
      'partner_profit_eur' => $model->partner_revshare_profit_eur,         //
      'revshare_ons' => $model->count_ons_revshare,  // RS ПДП
      'cpa_ons' => $model->getCpaOns(),
      'all_ons' => $model->getAllOns(),
      'clicks' => $model->getClicks(),
      'count_onetime' => $model->count_onetime,
      'count_sold' => $model->getSold(),
      'username' => $model->username,
      'countryname' => $model->countryname,
    ];
  }

  /**
   * Статистика доходов в разрезе валют за сегодня
   * @return array
   */
  public function getTodayRevenues()
  {
    $os = (new Query())->select([
      new Expression('SUM(IF(currency_id = 1, reseller_profit_rub, 0)) as reseller_profit_rub'),
      new Expression('SUM(IF(currency_id = 2, reseller_profit_usd, 0)) as reseller_profit_usd'),
      new Expression('SUM(IF(currency_id = 3, reseller_profit_eur, 0)) as reseller_profit_eur'),
      new Expression('SUM(IF(currency_id = 1, profit_rub, 0)) as profit_rub'),
      new Expression('SUM(IF(currency_id = 2, profit_usd, 0)) as profit_usd'),
      new Expression('SUM(IF(currency_id = 3, profit_eur, 0)) as profit_eur'),
    ])
      ->from('onetime_subscriptions as os')
      ->andFilterWhere(['user_id' => $this->users])
      ->andWhere(['date' => date('Y-m-d')]);

    $sr = (new Query())->select([
      new Expression('SUM(IF(currency_id = 1, `res_revshare_profit_rub`, 0)) AS res_revshare_profit_rub'),
      new Expression('SUM(IF(currency_id = 2, `res_revshare_profit_usd`, 0)) AS res_revshare_profit_usd'),
      new Expression('SUM(IF(currency_id = 3, `res_revshare_profit_eur`, 0)) AS res_revshare_profit_eur'),

      new Expression('SUM(IF(currency_id = 1, `res_sold_profit_rub`, 0)) AS res_sold_profit_rub'),
      new Expression('SUM(IF(currency_id = 2, `res_sold_profit_usd`, 0)) AS res_sold_profit_usd'),
      new Expression('SUM(IF(currency_id = 3, `res_sold_profit_eur`, 0)) AS res_sold_profit_eur'),

      new Expression('SUM(IF(currency_id = 1, `res_rejected_profit_rub`, 0)) AS res_rejected_profit_rub'),
      new Expression('SUM(IF(currency_id = 2, `res_rejected_profit_usd`, 0)) AS res_rejected_profit_usd'),
      new Expression('SUM(IF(currency_id = 3, `res_rejected_profit_eur`, 0)) AS res_rejected_profit_eur'),

      new Expression('SUM(IF(currency_id = 1, `partner_revshare_profit_rub`, 0)) AS partner_revshare_profit_rub'),
      new Expression('SUM(IF(currency_id = 2, `partner_revshare_profit_usd`, 0)) AS partner_revshare_profit_usd'),
      new Expression('SUM(IF(currency_id = 3, `partner_revshare_profit_eur`, 0)) AS partner_revshare_profit_eur'),
    ])
      ->from('statistic')
      ->andFilterWhere(['user_id' => $this->users])
      ->andWhere(['date' => date('Y-m-d')]);

    $ss = (new Query())
      ->select([
        'sold_partner_profit_rub' => 'SUM(IF(currency_id = 1, profit_rub, 0))',
        'sold_partner_profit_usd' => 'SUM(IF(currency_id = 2, profit_usd, 0))',
        'sold_partner_profit_eur' => 'SUM(IF(currency_id = 3, profit_eur, 0))',
      ])
      ->from(['st' => 'sold_subscriptions'])
      ->andWhere(['st.is_visible_to_partner' => 1])
      ->andFilterWhere(['user_id' => $this->users])
      ->andWhere(['date' => date('Y-m-d')]);

    $tb = (new Query())
      ->select([
        new Expression('SUM(`reseller_profit_rub`) AS res_sold_tb_profit_rub'),
        new Expression('SUM(`reseller_profit_usd`) AS res_sold_tb_profit_usd'),
        new Expression('SUM(`reseller_profit_eur`) AS res_sold_tb_profit_eur')
      ])
      ->from(['st' => 'sold_trafficback'])
      ->andFilterWhere(['user_id' => $this->users])
      ->andWhere(['date' => date('Y-m-d')]);

    $osData = $os->one();
    $srData = $sr->one();
    $ssData = $ss->one();
    $tbData = $tb->one();

    $resCPA[self::RUB] = ArrayHelper::getValue($osData, 'reseller_profit_rub', 0) +
      ArrayHelper::getValue($srData, 'res_sold_profit_rub', 0) +
      ArrayHelper::getValue($srData, 'res_rejected_profit_rub', 0);

    $resCPA[self::USD] = ArrayHelper::getValue($osData, 'reseller_profit_usd', 0) +
      ArrayHelper::getValue($srData, 'res_sold_profit_usd', 0) +
      ArrayHelper::getValue($srData, 'res_rejected_profit_usd', 0);

    $resCPA[self::EUR] = ArrayHelper::getValue($osData, 'reseller_profit_eur', 0) +
      ArrayHelper::getValue($srData, 'res_sold_profit_eur', 0) +
      ArrayHelper::getValue($srData, 'res_rejected_profit_eur', 0);

    $partnerCPA[self::RUB] = ArrayHelper::getValue($osData, 'profit_rub', 0) +
      ArrayHelper::getValue($ssData, 'sold_partner_profit_rub', 0);
    $partnerCPA[self::USD] = ArrayHelper::getValue($osData, 'profit_usd', 0) +
      ArrayHelper::getValue($ssData, 'sold_partner_profit_usd', 0);
    $partnerCPA[self::EUR] = ArrayHelper::getValue($osData, 'profit_eur', 0) +
      ArrayHelper::getValue($ssData, 'sold_partner_profit_eur', 0);

    $resRS[self::RUB] = ArrayHelper::getValue($srData, 'res_revshare_profit_rub', 0);
    $resRS[self::USD] = ArrayHelper::getValue($srData, 'res_revshare_profit_usd', 0);
    $resRS[self::EUR] = ArrayHelper::getValue($srData, 'res_revshare_profit_eur', 0);

    $partnerRS[self::RUB] = ArrayHelper::getValue($srData, 'partner_revshare_profit_rub', 0);
    $partnerRS[self::USD] = ArrayHelper::getValue($srData, 'partner_revshare_profit_usd', 0);
    $partnerRS[self::EUR] = ArrayHelper::getValue($srData, 'partner_revshare_profit_eur', 0);

    $resTb[self::RUB] = ArrayHelper::getValue($tbData, 'res_sold_tb_profit_rub', 0);
    $resTb[self::USD] = ArrayHelper::getValue($tbData, 'res_sold_tb_profit_usd', 0);
    $resTb[self::EUR] = ArrayHelper::getValue($tbData, 'res_sold_tb_profit_eur', 0);

    $resNet[self::RUB] = $resRS[self::RUB] + $resCPA[self::RUB] + $resTb[self::RUB] - $partnerRS[self::RUB] - $partnerCPA[self::RUB];
    $resNet[self::USD] = $resRS[self::USD] + $resCPA[self::USD] + $resTb[self::USD] - $partnerRS[self::USD] - $partnerCPA[self::USD];
    $resNet[self::EUR] = $resRS[self::EUR] + $resCPA[self::EUR] + $resTb[self::EUR] - $partnerRS[self::EUR] - $partnerCPA[self::EUR];

    return [
      'resRS' => [
        self::RUB => $resRS[self::RUB],
        self::USD => $resRS[self::USD],
        self::EUR => $resRS[self::EUR]
      ],
      'resCPA' => [
        self::RUB => $resCPA[self::RUB],
        self::USD => $resCPA[self::USD],
        self::EUR => $resCPA[self::EUR]
      ],
      'resGross' => [
        self::RUB => $resRS[self::RUB] + $resCPA[self::RUB] + $resTb[self::RUB],
        self::USD => $resRS[self::USD] + $resCPA[self::USD] + $resTb[self::USD],
        self::EUR => $resRS[self::EUR] + $resCPA[self::EUR] + $resTb[self::EUR],
      ],
      'resNet' => [
        self::RUB => $resNet[self::RUB],
        self::USD => $resNet[self::USD],
        self::EUR => $resNet[self::EUR],
      ],
    ];
  }

}