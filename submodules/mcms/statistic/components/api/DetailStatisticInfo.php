<?php

namespace mcms\statistic\components\api;

use mcms\common\module\api\ApiResult;
use mcms\statistic\components\exception\DetailModelNotLoadedException;
use mcms\statistic\components\exception\InvalidRecordIdException;
use mcms\statistic\components\exception\InvalidStatisticTypeException;
use mcms\statistic\components\exception\WrongStatisticTypeException;
use mcms\statistic\models\mysql\DetailStatistic as DetailStatisticModel;
use mcms\statistic\models\mysql\DetailStatisticComplains;
use mcms\statistic\models\mysql\DetailStatisticHit;
use mcms\statistic\models\mysql\DetailStatisticIK;
use mcms\statistic\models\mysql\DetailStatisticSells;
use mcms\statistic\models\mysql\DetailStatisticSubscriptions;
use yii\helpers\ArrayHelper;

class DetailStatisticInfo extends ApiResult
{
  private $recordId;
  private $statisticType;

  function init($params = [])
  {
    $this->recordId = (int)ArrayHelper::getValue($params, 'id');
    $this->statisticType = ArrayHelper::getValue($params, 'statisticType');

    if (!$this->recordId) throw new InvalidRecordIdException;
    if ($this->statisticType === null) throw new InvalidStatisticTypeException;

    $this->validateStatisticType();
  }

  private function validateStatisticType()
  {
    if (!in_array($this->statisticType, [
      DetailStatisticModel::GROUP_SUBSCRIPTIONS,
      DetailStatisticModel::GROUP_IK,
      DetailStatisticModel::GROUP_SELLS,
      DetailStatisticComplains::GROUP_NAME,
      DetailStatisticHit::GROUP_NAME,
    ])) {
      throw new WrongStatisticTypeException;
    }
  }

  public function getResult()
  {
    return $this->getLoadedRecord();
  }


  private function getLoadedRecord()
  {
    $record = null;
    $model = null;
    switch ($this->statisticType) {
      case DetailStatisticModel::GROUP_SUBSCRIPTIONS:
        $model = new DetailStatisticSubscriptions;
        $record = $model->findOne($this->recordId);
        break;
      case DetailStatisticModel::GROUP_IK:
        $model = new DetailStatisticIK();
        $record = $model->findOne($this->recordId);
        break;
      case DetailStatisticComplains::GROUP_NAME:
        $model = new DetailStatisticComplains();
        $record = $model->findOne($this->recordId);
        break;
      case DetailStatisticModel::GROUP_SELLS:
        $model = new DetailStatisticSells();
        $record = $model->findOne($this->recordId);
        break;
      case DetailStatisticHit::GROUP_NAME:
        $model = new DetailStatisticHit();
        $record = $model->findOne($this->recordId);
        break;
    }

    if ($record === null) {
      throw new DetailModelNotLoadedException;
    }

    return [$model, $record];
  }

}