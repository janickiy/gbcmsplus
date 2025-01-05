<?php

namespace mcms\statistic\components\api;

use mcms\common\module\api\ApiResult;
use mcms\statistic\models\mysql\Statistic;
use Yii;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;

class MainStatistic extends ApiResult
{

  /** @var \mcms\statistic\models\mysql\Statistic */
  private $model;
  private $requestData;
  private $pagination;

  private $result = null;

  private $type;

  function init($params = [])
  {
    $this->requestData = ArrayHelper::getValue($params, 'requestData', []);
    $this->filterData($this->requestData);
    $this->type = ArrayHelper::getValue($params, 'type', 'default');
    $this->pagination = ArrayHelper::getValue($params, 'pagination');
    $this->model = Yii::$app->getModule('statistic')->getStatisticModel($this->requestData, $this->type);
    if ($viewerId = ArrayHelper::getValue($params, 'viewerId')) {
      $this->model->setViewerId($viewerId);
    }
    $this->setResultTypeDataProvider();
  }

  public function getResult()
  {
    $this->setDataProvider($this->model->getStatisticGroup());
    if (isset($this->pagination)) {
      $this->dataProvider->setPagination($this->pagination);
    }
    return parent::getResult();
  }

  public function getModel()
  {
    return $this->model;
  }

  public function getGroupStatistic()
  {
    if ($this->result === null) {
      $this->result = $this->getResult();
    }
    return [$this->model, $this->result];
  }

  /**
   * @see Statistic::canViewAdminProfit()
   */
  public function canViewAdminProfit()
  {
    return $this->model->canViewAdminProfit();
  }

  /**
   * @see Statistic::canViewResellerProfit()
   */
  public function canViewResellerProfit()
  {
    return $this->model->canViewResellerProfit();
  }

  /**
   * @see Statistic::canViewPartnerProfit()
   */
  public function canViewPartnerProfit()
  {
    return $this->model->canViewPartnerProfit();
  }
}