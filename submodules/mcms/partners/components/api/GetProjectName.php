<?php

namespace mcms\partners\components\api;

use mcms\common\module\api\ApiResult;
use mcms\partners\Module;
use Yii;
use yii\helpers\BaseInflector;

/**
 * Class GetProjectName
 * @package mcms\partners\components\api
 */
class GetProjectName extends ApiResult
{
  /**
   * @inheritdoc
   */
  function init($params = [])
  {

  }

  /**
   * @inheritdoc
   */
  public function getResult()
  {
    return Yii::$app->getModule('partners')
      ->settings
      ->getValueByKey(Module::SETTINGS_PROJECT_NAME)
      ;
  }

  /**
   * @return string
   */
  public function getWcZipFileName()
  {
    $projName = $this->getResult();
    return BaseInflector::slug($projName, '_') . '.zip';
  }
}