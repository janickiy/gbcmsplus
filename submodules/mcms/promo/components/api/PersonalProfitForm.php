<?php


namespace mcms\promo\components\api;


use mcms\common\module\api\ApiResult;
use mcms\promo\components\widgets\PersonalProfitWidget;
use Yii;

class PersonalProfitForm extends ApiResult
{
  function init($params = [])
  {
    $this->prepareWidget(PersonalProfitWidget::class, $params);
  }
}