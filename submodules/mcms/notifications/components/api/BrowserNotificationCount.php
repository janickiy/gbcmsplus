<?php
namespace mcms\notifications\components\api;

use mcms\common\module\api\ApiResult;
use mcms\modmanager\models\Module;
use Yii;


class BrowserNotificationCount extends ApiResult
{
  protected $userId;

  public function init($params = [])
  {

  }

  public function getResult()
  {
    $result = [];
    /** @var Module $moduleModel */
    foreach (Module::findEnabled() as $moduleModel) {
      /** @var \mcms\common\module\Module $module */
      if (!$module = Yii::$app->getModule($moduleModel->getModuleId())) {
        continue;
      }
      if (!$module->apiClasses) continue;
      if (!array_key_exists('badgeCounters', $module->apiClasses)) continue;
      $badgeCounters = $module->api('badgeCounters')->getResult();

      if (is_array($badgeCounters) && count($badgeCounters)) {
        $result = array_merge($result, $badgeCounters);
      }
    }
    return $result;
  }

}