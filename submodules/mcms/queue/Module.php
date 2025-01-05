<?php

namespace mcms\queue;

use Yii;
use yii\console\Application as ConsoleApplication;

/**
 * Модуль расширяющий возможности модуля rgk\queue
 */
class Module extends \mcms\common\module\Module
{
  public $controllerNamespace = 'mcms\queue\controllers';

  public function init()
  {
    parent::init();

    if (Yii::$app instanceof ConsoleApplication) {
      $this->controllerNamespace = 'mcms\queue\commands';
    }
  }
}
