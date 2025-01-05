<?php

namespace mcms\statistic\components\api;

use mcms\common\module\api\ApiResult;
use Yii;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;

class TBStatistic extends ApiResult
{

  /** @var \mcms\statistic\models\mysql\TBStatistic */
  private $model;
  private $requestData;

  private $result = null;

  function init($params = [])
  {
    $this->requestData = ArrayHelper::getValue($params, 'requestData', []);
    $this->filterData($this->requestData);
    $this->model = Yii::$app->getModule('statistic')->getStatisticModel($this->requestData, 'tb');
    $this->setDataProvider($this->model->getStatisticGroup());
    $this->setResultTypeDataProvider();
  }

  public function getGroupStatistic()
  {
    if ($this->result === null) {
      $this->result = $this->getResult();
    }
    return [$this->model, $this->result];
  }
}