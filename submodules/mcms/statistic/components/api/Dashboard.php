<?php

namespace mcms\statistic\components\api;

use mcms\common\module\api\ApiResult;
use mcms\statistic\components\DashboardData;
use mcms\statistic\models\DashboardProfitsOns;
use yii\helpers\ArrayHelper;

/**
 * Получение данных для дашборда
 * Class Dashboard
 * @package mcms\statistic\components\api
 */
class Dashboard extends ApiResult
{
  private $_startDate;
  private $_endDate;
  private $_countries;
  private $_operators;
  private $_users;

  private $_dashboardData;
  private $_todayRevenues;


  /**
   * @inheritdoc
   */
  function init($params = [])
  {
    $this->_startDate = ArrayHelper::getValue($params, 'startDate', null);
    $this->_endDate = ArrayHelper::getValue($params, 'endDate', null);
    $this->_countries = ArrayHelper::getValue($params, 'countries', null);
    $this->_operators = ArrayHelper::getValue($params, 'operators', null);
    $this->_users = ArrayHelper::getValue($params, 'users', null);
  }

  /**
   * Получение топа лендингов
   * @return array
   */
  public function getLandings()
  {
    return $this->getDashboardData()->getLandings();
  }

  /**
   * Вся статистика с группировкой по дням
   * @return array
   */
  public function getStatByDates()
  {
    return $this->getDashboardData()->getStat(DashboardProfitsOns::STAT_BY_DATE);
  }

  /**
   * Статистика с группировкой по партнерам
   * @return array
   */
  public function getStatByUsers()
  {
    return $this->getDashboardData()->getStat(DashboardProfitsOns::STAT_BY_PARTNERS);
  }

  /**
   * Статистика с группировкой по странам
   * @return array
   */
  public function getStatByCountries()
  {
    return $this->getDashboardData()->getStat(DashboardProfitsOns::STAT_BY_COUNTRY);
  }

  /**
   * Количество активных партнеров по датам
   * @return array
   */
  public function getActivePartners()
  {
    return $this->getDashboardData()->getActivePartners();
  }

  /**
   * Статистика доходов в разрезе валют
   * @return array
   */
  public function getTodayRevenues()
  {
    if ($this->_todayRevenues) return $this->_todayRevenues;
    return $this->_todayRevenues = $this->getDashboardData()->getTodayRevenues();
  }

  /**
   * Получение закешированного экземпляра объекта DashboardData
   * @return DashboardData
   */
  private function getDashboardData()
  {
    if ($this->_dashboardData) return $this->_dashboardData;
    return $this->_dashboardData = new DashboardData([
      'startDate' => $this->_startDate,
      'endDate' => $this->_endDate,
      'countries' => $this->_countries,
      'operators' => $this->_operators,
      'users' => $this->_users,
    ]);
  }
}
