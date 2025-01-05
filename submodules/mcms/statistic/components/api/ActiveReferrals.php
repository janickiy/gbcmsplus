<?php

namespace mcms\statistic\components\api;

use Yii;
use mcms\common\module\api\ApiResult;
use mcms\statistic\models\mysql\UserDayGroupStatistic as StatisticModel;
use yii\helpers\ArrayHelper;
use yii\base\InvalidParamException;

class ActiveReferrals extends ApiResult
{

  private $_userId;
  private $_startDate;
  private $_endDate;

  function init($params = [])
  {
    $this->_userId = ArrayHelper::getValue($params, 'userId', null);
    $this->_startDate = ArrayHelper::getValue($params, 'startDate', null);
    $this->_endDate = ArrayHelper::getValue($params, 'endDate', null);

    if ($this->_userId === null) {throw new InvalidParamException('User id is missing');}
  }

  public function getResult()
  {
    return (new StatisticModel([
      'userId' => $this->_userId,
      'startDate' => $this->_startDate,
      'endDate' => $this->_endDate
    ]))->getActiveReferralsIds();
  }

  public function getCount()
  {
    return (new StatisticModel([
      'userId' => $this->_userId,
      'startDate' => $this->_startDate,
      'endDate' => $this->_endDate
    ]))->getActiveReferralsCount();
  }

}
