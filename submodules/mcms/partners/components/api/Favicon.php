<?php

namespace mcms\partners\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\partners\Module;
use Yii;

class Favicon extends ApiResult
{
  const DEFAULT_MIME_TYPE = 'image/x-icon';

  function init($params = []){}

  public function getResult()
  {
    return Yii::$app->getModule('partners')->settings->offsetGet(Module::SETTINGS_FAVICON)->getUrl();
  }

  public function getIconMimeType()
  {
    $iconPath = Yii::$app->getModule('partners')->settings->offsetGet(Module::SETTINGS_FAVICON)->getFilePath();
    try {
      return ArrayHelper::getValue(getimagesize($iconPath), 'mime', self::DEFAULT_MIME_TYPE);
    } catch(\Exception $e) {
      return self::DEFAULT_MIME_TYPE;
    }
  }
}