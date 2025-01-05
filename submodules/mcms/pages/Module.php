<?php
namespace mcms\pages;

use yii\console\Application as ConsoleApplication;
use Yii;

class Module extends \mcms\common\module\Module
{
  public $controllerNamespace = 'mcms\pages\controllers';
  public $url;
  public $name;
  public $menu;

  public function init()
  {
    parent::init();

    if (Yii::$app instanceof ConsoleApplication) {
      $this->controllerNamespace = 'mcms\pages\commands';
    }
  }
}