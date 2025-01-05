<?php


namespace mcms\promo\components\api;


use mcms\common\module\api\ApiResult;
use mcms\promo\components\widgets\TrafficBlockWidget;

class TrafficBlockForm extends ApiResult
{
  function init($params = [])
  {
    $this->prepareWidget(TrafficBlockWidget::class, $params);
  }
}