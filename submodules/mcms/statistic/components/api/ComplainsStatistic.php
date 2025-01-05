<?php

namespace mcms\statistic\components\api;

use mcms\common\module\api\ApiResult;
use mcms\statistic\models\mysql\DetailStatisticComplains;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Получение модели статистики по жалобам
 */
class ComplainsStatistic extends ApiResult
{

  /** @var \mcms\statistic\models\mysql\DetailStatisticComplains */
  private $model;
  private $requestData;

  private $result = null;

  function init($params = [])
  {
    $this->requestData = ArrayHelper::getValue($params, 'requestData', []);
    $this->filterData($this->requestData);
    $this->model = Yii::$app->getModule('statistic')->getStatisticModel($this->requestData, DetailStatisticComplains::STATISTIC_NAME);
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