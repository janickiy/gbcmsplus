<?php

namespace mcms\statistic\components\api;

use mcms\common\module\api\ApiResult;
use mcms\statistic\models\mysql\DetailStatisticIK;
use mcms\statistic\models\mysql\DetailStatisticSells;
use mcms\statistic\models\mysql\DetailStatisticSubscriptions;
use Yii;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;

class DetailStatistic extends ApiResult
{

  /** @var \mcms\statistic\models\mysql\DetailStatistic */
  private $model;
  private $requestData;
  private $type;

  private $result = null;

  function init($params = [])
  {
    $this->requestData = ArrayHelper::getValue($params, 'requestData', []);
    $this->type = ArrayHelper::getValue($params, 'type', 'detail');
    $this->filterData($this->requestData);
    $this->model = Yii::$app->getModule('statistic')->getStatisticModel($this->requestData, $this->type);
    $this->setDataProvider($this->model->getStatisticGroup());
    $this->setResultTypeDataProvider();
  }

  public function getTypeSubscriptions()
  {
    return DetailStatisticSubscriptions::GROUP_NAME;
  }

  public function getTypeIk()
  {
    return DetailStatisticIK::GROUP_NAME;
  }

  public function getTypeSells()
  {
    return DetailStatisticSells::GROUP_NAME;
  }

  public function getGroupStatistic()
  {
    if ($this->result === null) {
      $this->result = $this->getResult();
    }
    return [$this->model, $this->result];
  }
}