<?php

namespace mcms\promo\components\api;


use mcms\common\module\api\ApiResult;
use mcms\promo\Module;
use Yii;

class Settings extends ApiResult
{

  function init($params = [])
  {

  }

  public function getDefaultClickNConfirmText()
  {
    return Yii::$app->getModule('promo')->getDefaultClickNConfirmText();
  }
}