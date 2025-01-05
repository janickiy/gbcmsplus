<?php

namespace mcms\modmanager\commands;

use mcms\modmanager\models\Module;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Init module manager
 *
 */
class InitController extends Controller
{
  /**
   * Init module manager
   */
  public function actionInit()
  {
    $module = new Module();
    $module->module_id = 'users';
    $module->name = 'app.common.module_users';
    $module->is_disabled = 0;
    $module->save();

    $this->stdout('Module manager init finished' . "!\n", Console::FG_GREEN);
  }
}