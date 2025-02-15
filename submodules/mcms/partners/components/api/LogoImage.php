<?php

namespace mcms\partners\components\api;

use mcms\common\module\api\ApiResult;
use mcms\partners\Module;
use Yii;

class LogoImage extends ApiResult
{
  function init($params = []){}

  public function getResult()
  {
    return Yii::$app->getModule('partners')->settings->offsetGet(Module::SETTINGS_LOGO_IMAGE)->getUrl();
  }
}