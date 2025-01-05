<?php

namespace mcms\statistic\components\api;

use mcms\common\module\api\ApiResult;
use mcms\statistic\models\mysql\DetailStatisticSells;

class SoldSubscriptions extends ApiResult
{
  /** @var \mcms\statistic\models\mysql\DetailStatisticSells */
  private $model;

  public function init($params = [])
  {
    $this->model = new DetailStatisticSells();
    $this->model->attributes = $params;

    return $this;
  }

  public function setAttributes($attributes)
  {
    $this->model->attributes = $attributes;
  }

  public function getCounts()
  {
    $this->setDataProvider($this->model->getCounts());
    $this->setResultTypeArray();

    return $this;
  }

  public function getSums()
  {
    $this->setDataProvider($this->model->getSums());
    $this->setResultTypeArray();

    return $this;
  }

}