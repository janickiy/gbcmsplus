<?php

namespace mcms\payments\components\widgets\assets;

use yii\web\AssetBundle;

class UserSettingsAsset extends AssetBundle
{
  public $sourcePath = __DIR__ ;

  public $css = [

  ];
  public $js = [
    'js/user_settings.js'
  ];
  public $depends = [
    'yii\web\YiiAsset',
    'yii\bootstrap\BootstrapAsset',
    'mcms\common\assets\DirtyForms',
  ];
}