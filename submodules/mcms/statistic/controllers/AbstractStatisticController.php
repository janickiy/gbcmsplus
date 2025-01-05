<?php

namespace mcms\statistic\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\statistic\components\AbstractStatistic;
use Yii;

/**
 * Общие функции для контроллеров статистики
 */
abstract class AbstractStatisticController extends AdminBaseController
{
  private $_countries;

  /**
   * @param AbstractStatistic $model
   * @param string $currency
   * @return array ['id' => 'name']
   */
  protected function getCountries(AbstractStatistic $model, $currency = null)
  {
    if ($this->_countries) {
      return $this->_countries;
    }
    $this->_countries = $model->getCountries($currency);

    return $this->_countries;
  }

}