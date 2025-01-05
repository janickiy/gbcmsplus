<?php

namespace mcms\loyalty;

use Yii;
use yii\console\Application as ConsoleApplication;

/**
 * Модуль для работы с программой лояльности для реселлеров
 */
class Module extends \mcms\common\module\Module
{
  public $controllerNamespace = 'mcms\loyalty\controllers';

  public function init()
  {
    parent::init();

    if (Yii::$app instanceof ConsoleApplication) {
      $this->controllerNamespace = 'mcms\loyalty\commands';
    }
  }
}
