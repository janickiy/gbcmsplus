<?php

namespace mcms\statistic\components\api;

use mcms\common\module\api\ApiResult;
use mcms\statistic\models\mysql\Analytics;
use mcms\statistic\Module;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class AnalyticsApi
 * @package mcms\statistic\components\api
 */
class AnalyticsApi extends ApiResult
{

  const DEFAULT_PAGE_SIZE = 1000;

  /** @var \mcms\statistic\models\mysql\Analytics */
  private $model;
  private $requestData;
  private $type;

  private $result = null;

  /**
   * @inheritdoc
   */
  function init($params = [])
  {
    $this->requestData = ArrayHelper::getValue($params, 'requestData', []);
    $this->type = ArrayHelper::getValue($params, 'type', Analytics::STATISTIC_NAME);
    $this->filterData($this->requestData);
    /** @var Module $statModule */
    $statModule = Yii::$app->getModule('statistic');
    $this->model = $statModule->getStatisticModel($this->requestData, $this->type);
    $dataProvider = $this->model->getStatisticGroup();
    $dataProvider->setPagination(['pageSize' => self::DEFAULT_PAGE_SIZE]);
    $this->setDataProvider($dataProvider);
    $this->setResultTypeDataProvider();
  }

  /**
   * @return array
   */
  public function getResult()
  {
    if ($this->result === null) {
      $this->result = parent::getResult();
    }
    return [$this->model, $this->result];
  }
}