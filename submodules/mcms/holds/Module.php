<?php

namespace mcms\holds;

use yii\console\Application as ConsoleApplication;
use Yii;

/**
 * Class Module
 * @package mcms\holds
 */
class Module extends \mcms\common\module\Module
{
  public $controllerNamespace = 'mcms\holds\controllers';

  public function init()
  {
    parent::init();

    if (Yii::$app instanceof ConsoleApplication) {
      $this->controllerNamespace = 'mcms\holds\commands';
    }
  }

  /**
   * Разрешено ли видеть дату последнего расхолда
   * @return bool
   */
  public function canViewLastUnholdDate()
  {
    return Yii::$app->user->can('CanViewLastUnholdDate');
  }
}
