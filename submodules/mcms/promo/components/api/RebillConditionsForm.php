<?php
namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\components\widgets\RebillConditionsWidget;
use Yii;

class RebillConditionsForm extends ApiResult
{
  function init($params = [])
  {
    $this->prepareWidget(RebillConditionsWidget::class, $params);
  }
}