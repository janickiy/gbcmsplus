<?php

namespace mcms\partners\commands;

use Yii;
use mcms\partners\rbac\ItsMyTempFile;
use mcms\partners\Module;
use yii\console\Controller;


class RbacController extends Controller
{

  /**
   * Create permissions
   */
  public function actionInit()
  {
   
    $authManager = Yii::$app->authManager;
    $ItsMyTempFileRule = new ItsMyTempFile();

    $authManager->add($ItsMyTempFileRule);

    $authManager->add(new \yii\rbac\Permission([
      'name' => Module::PERMISSION_DELETE_OWN_TEMP_FILE,
      'description' => 'Can delete own  temp files',
    ]));

  }

}