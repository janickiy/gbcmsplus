<?php

namespace mcms\currency;

use Yii;
use yii\console\Application as ConsoleApplication;

class Module extends \mcms\common\module\Module
{
  public $controllerNamespace = 'mcms\currency\controllers';
  public function init()
  {
    parent::init();

    if (Yii::$app instanceof ConsoleApplication) {
      $this->controllerNamespace = 'mcms\currency\commands';
    }
  }
}