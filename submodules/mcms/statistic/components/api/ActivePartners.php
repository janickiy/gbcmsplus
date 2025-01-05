<?php

namespace mcms\statistic\components\api;

use Yii;
use mcms\common\module\api\ApiResult;
use mcms\statistic\models\mysql\UserDayGroupStatistic as StatisticModel;
use yii\helpers\ArrayHelper;
use yii\base\InvalidParamException;

/**
 * Активные партнеры
 * Class ActivePartners
 * @package mcms\statistic\components\api
 */
class ActivePartners extends ApiResult
{
  private $_startDate;
  private $_endDate;
  private $_userId;

  /**
   * @inheritdoc
   */
  function init($params = [])
  {
    $this->_startDate = ArrayHelper::getValue($params, 'startDate', null);
    $this->_endDate = ArrayHelper::getValue($params, 'endDate', null);
    $this->_userId = ArrayHelper::getValue($params, 'userId', null);
    if (!$this->_userId) {
      throw new \Exception('No user id was set');
    }
  }

  /**
   * Кол-во активных партнеров
   * @return array массив вида $arr['yyyy-mm-dd'] = $count
   */
  public function getCount()
  {
    return (new StatisticModel([
      'startDate' => $this->_startDate,
      'endDate' => $this->_endDate,
      'userId' => $this->_userId,
    ]))->getActivePartnersCount();
  }

}
