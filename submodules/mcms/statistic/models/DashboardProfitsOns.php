<?php

namespace mcms\statistic\models;

use mcms\statistic\components\DashboardData;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class DashboardProfitsOns
 * @package mcms\statistic\models
 */
class DashboardProfitsOns extends Model
{
  public $countryname;
  public $username;
  public $date;
  public $user_id;
  public $country_id;
  public $res_revshare_profit_rub;
  public $res_revshare_profit_usd;
  public $res_revshare_profit_eur;
  public $res_rejected_profit_rub;
  public $res_rejected_profit_usd;
  public $res_rejected_profit_eur;
  public $res_sold_profit_rub;
  public $res_sold_profit_usd;
  public $res_sold_profit_eur;
  public $res_onetime_profit_rub;
  public $res_onetime_profit_usd;
  public $res_onetime_profit_eur;
  public $res_sold_tb_profit_rub;
  public $res_sold_tb_profit_usd;
  public $res_sold_tb_profit_eur;
  public $partner_revshare_profit_rub;
  public $partner_revshare_profit_usd;
  public $partner_revshare_profit_eur;
  public $partner_onetime_profit_rub;
  public $partner_onetime_profit_usd;
  public $partner_onetime_profit_eur;
  public $partner_sold_profit_rub;
  public $partner_sold_profit_usd;
  public $partner_sold_profit_eur;
  public $partner_sold_tb_profit_rub;
  public $partner_sold_tb_profit_usd;
  public $partner_sold_tb_profit_eur;
  public $count_ons_revshare;
  public $count_ons_rejected;
  public $count_ons_cpa;
  public $count_onetime;
  public $count_hits;

  const STAT_BY_DATE = 'date';
  const STAT_BY_PARTNERS = 'user_id';
  const STAT_BY_COUNTRY = 'country_id';

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'dashboard_profits_ons';
  }

  /**
   * @param $statType
   * @param $startDate
   * @param $endDate
   * @param array $countries
   * @param array $operators
   * @param array $users
   * @return array
   */
  public static function findAll($statType, $startDate, $endDate, $countries = [], $operators = [], $users = [])
  {
    switch ($statType) {
      case self::STAT_BY_DATE:
        return self::findDates($startDate, $endDate, $countries, $operators, $users);
        break;
      case self::STAT_BY_PARTNERS:
        return self::findPartners($startDate, $endDate, $countries, $operators, $users);
        break;
      case self::STAT_BY_COUNTRY:
        return self::findCountries($startDate, $endDate, $countries, $operators, $users);
        break;
    }
  }

  /**
   * Вся статистика с группировкой по дням
   * @param $startDate
   * @param $endDate
   * @param array $countries
   * @param array $operators
   * @param array $users
   * @return array
   */
  private static function findDates($startDate, $endDate, $countries = [], $operators = [], $users = [])
  {
    return (new Query())->select(self::getColumns([self::tableName() . '.date as date']))
      ->from(self::tableName())
      ->andWhere(['BETWEEN', 'date', $startDate, $endDate])
      ->andFilterWhere(['country_id' => $countries])
      ->andFilterWhere(['operator_id' => $operators])
      ->andFilterWhere(['user_id' => $users])
      ->groupBy(['date'])->all();
  }

  /**
   * Статистика с группировкой по партнерам
   * @param $startDate
   * @param $endDate
   * @param array $countries
   * @param array $operators
   * @param array $users
   * @return array
   */
  private static function findPartners($startDate, $endDate, $countries = [], $operators = [], $users = [])
  {
    return (new Query())->select(self::getColumns([self::tableName() . '.user_id as user_id', 'users.username as username']))
      ->from(self::tableName())
      ->leftJoin('users', 'user_id = users.id')
      ->andWhere(['BETWEEN', 'date', $startDate, $endDate])
      ->andFilterWhere(['country_id' => $countries])
      ->andFilterWhere(['operator_id' => $operators])
      ->andFilterWhere(['user_id' => $users])
      ->groupBy(['user_id'])->all();
  }

  /**
   * Статистика с группировкой по странам
   * @param $startDate
   * @param $endDate
   * @param array $countries
   * @param array $operators
   * @param array $users
   * @return array
   */
  private static function findCountries($startDate, $endDate, $countries = [], $operators = [], $users = [])
  {
    return (new Query())->select(self::getColumns([self::tableName() . '.country_id as country_id', 'countries.name as countryname']))
      ->from(self::tableName())
      ->leftJoin('countries', self::tableName() . '.country_id = countries.id')
      ->andWhere(['BETWEEN', 'date', $startDate, $endDate])
      ->andFilterWhere(['country_id' => $countries])
      ->andFilterWhere(['operator_id' => $operators])
      ->andFilterWhere(['user_id' => $users])
      ->groupBy(['country_id'])->all();
  }

  /**
   * Количество активных партнеров по дням
   * @param $startDate
   * @param $endDate
   * @param array $countries
   * @param array $operators
   * @param array $users
   * @return array
   */
  public static function findActivePartnersCount($startDate, $endDate, $countries = [], $operators = [], $users = [])
  {
    return (new Query())->select([
      'date',
      new Expression('COUNT(DISTINCT ' . self::tableName() . '.user_id) as count')
    ])
      ->from(self::tableName())
      ->andWhere(['BETWEEN', 'date', $startDate, $endDate])
      ->andWhere(['>', 'count_hits', 0])
      ->andFilterWhere(['country_id' => $countries])
      ->andFilterWhere(['operator_id' => $operators])
      ->andFilterWhere(['user_id' => $users])
      ->groupBy(['date'])->all();
  }

  /**
   * Ресовский валовый доход
   * @param string $currency
   * @return float
   */
  public function getResGrossRevenue($currency)
  {
    $result = $this->{'res_revshare_profit_' . $currency} +
      $this->{'res_sold_profit_' . $currency} +
      $this->{'res_rejected_profit_' . $currency} +
      $this->{'res_onetime_profit_' . $currency} +
      $this->{'res_sold_tb_profit_' . $currency};
    return round($result, 2);
  }

  /**
   * Партнерский валовый доход
   * @param string $currency
   * @return float
   */
  public function getPartnerGrossRevenue($currency)
  {
    $result = $this->{'partner_onetime_profit_' . $currency} +
      $this->{'partner_revshare_profit_' . $currency} +
      $this->{'partner_sold_tb_profit_' . $currency} +
      $this->{'partner_sold_profit_' . $currency};
    return round($result, 2);
  }

  /**
   * Реселлерский чистый доход
   * @param string $currency
   * @return float
   */
  public function getResNetRevenue($currency)
  {
    $result = $this->getResGrossRevenue($currency) - $this->getPartnerGrossRevenue($currency);
    return round($result, 2);
  }

  /**
   * Реселлерская CPA прибыль
   * @param $currency
   * @return float
   */
  public function getResCpaProfit($currency)
  {
    $result = $this->{'res_onetime_profit_' . $currency} + $this->{'res_sold_profit_' . $currency};
    return round($result, 2);
  }

  /**
   * Партнерская CPA прибыль
   * @param $currency
   * @return float
   */
  public function getPartnerCpaProfit($currency)
  {
    $result = $this->{'partner_onetime_profit_' . $currency} +
      $this->{'partner_sold_profit_' . $currency};
    return round($result, 2);
  }

  /**
   * CPA подписки
   * @return int
   */
  public function getCpaOns()
  {
    return $this->count_onetime + $this->getSold();
  }

  /**
   * Все подписки
   * @return int
   */
  public function getAllOns()
  {
    return $this->count_onetime + $this->count_ons_cpa + $this->count_ons_revshare;
  }

  /**
   * Клики
   * @return int
   */
  public function getClicks()
  {
    return $this->count_hits;
  }

  /**
   * Солды
   * @return int
   */
  public function getSold()
  {
    return $this->count_ons_cpa - $this->count_ons_rejected;
  }

  /**
   * Длинный список общих колонок
   * @param array $additionalColumns
   * @return array
   */
  private static function getColumns($additionalColumns = [])
  {
    return array_merge($additionalColumns, [
      new Expression('SUM(' . self::tableName() . '.res_revshare_profit_rub) as res_revshare_profit_rub'),
      new Expression('SUM(' . self::tableName() . '.res_revshare_profit_usd) as res_revshare_profit_usd'),
      new Expression('SUM(' . self::tableName() . '.res_revshare_profit_eur) as res_revshare_profit_eur'),
      new Expression('SUM(' . self::tableName() . '.res_rejected_profit_rub) as res_rejected_profit_rub'),
      new Expression('SUM(' . self::tableName() . '.res_rejected_profit_usd) as res_rejected_profit_usd'),
      new Expression('SUM(' . self::tableName() . '.res_rejected_profit_eur) as res_rejected_profit_eur'),
      new Expression('SUM(' . self::tableName() . '.res_sold_profit_rub) as res_sold_profit_rub'),
      new Expression('SUM(' . self::tableName() . '.res_sold_profit_usd) as res_sold_profit_usd'),
      new Expression('SUM(' . self::tableName() . '.res_sold_profit_eur) as res_sold_profit_eur'),
      new Expression('SUM(' . self::tableName() . '.res_onetime_profit_rub) as res_onetime_profit_rub'),
      new Expression('SUM(' . self::tableName() . '.res_onetime_profit_usd) as res_onetime_profit_usd'),
      new Expression('SUM(' . self::tableName() . '.res_onetime_profit_eur) as res_onetime_profit_eur'),
      new Expression('SUM(' . self::tableName() . '.res_sold_tb_profit_rub) as res_sold_tb_profit_rub'),
      new Expression('SUM(' . self::tableName() . '.res_sold_tb_profit_usd) as res_sold_tb_profit_usd'),
      new Expression('SUM(' . self::tableName() . '.res_sold_tb_profit_eur) as res_sold_tb_profit_eur'),
      new Expression('SUM(' . self::tableName() . '.partner_revshare_profit_rub) as partner_revshare_profit_rub'),
      new Expression('SUM(' . self::tableName() . '.partner_revshare_profit_usd) as partner_revshare_profit_usd'),
      new Expression('SUM(' . self::tableName() . '.partner_revshare_profit_eur) as partner_revshare_profit_eur'),
      new Expression('SUM(' . self::tableName() . '.partner_onetime_profit_rub) as partner_onetime_profit_rub'),
      new Expression('SUM(' . self::tableName() . '.partner_onetime_profit_usd) as partner_onetime_profit_usd'),
      new Expression('SUM(' . self::tableName() . '.partner_onetime_profit_eur) as partner_onetime_profit_eur'),
      new Expression('SUM(' . self::tableName() . '.partner_sold_profit_rub) as partner_sold_profit_rub'),
      new Expression('SUM(' . self::tableName() . '.partner_sold_profit_usd) as partner_sold_profit_usd'),
      new Expression('SUM(' . self::tableName() . '.partner_sold_profit_eur) as partner_sold_profit_eur'),
      new Expression('SUM(' . self::tableName() . '.partner_sold_tb_profit_rub) as partner_sold_tb_profit_rub'),
      new Expression('SUM(' . self::tableName() . '.partner_sold_tb_profit_usd) as partner_sold_tb_profit_usd'),
      new Expression('SUM(' . self::tableName() . '.partner_sold_tb_profit_eur) as partner_sold_tb_profit_eur'),
      new Expression('SUM(' . self::tableName() . '.count_ons_cpa) as count_ons_cpa'),
      new Expression('SUM(' . self::tableName() . '.count_ons_revshare) as count_ons_revshare'),
      new Expression('SUM(' . self::tableName() . '.count_onetime) as count_onetime'),
      new Expression('SUM(' . self::tableName() . '.count_ons_rejected) as count_ons_rejected'),
      new Expression('SUM(' . self::tableName() . '.count_hits) as count_hits'),
    ]);
  }
}
