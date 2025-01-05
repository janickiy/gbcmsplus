<?php

namespace mcms\partners\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/resources/default';

  public $css = [
    'css/style.css',
  ];
  public $js = [
    'js/main.js',
  ];
  public $depends = [
    'yii\web\YiiAsset',
    'yii\bootstrap\BootstrapAsset',
    'branchonline\lightbox\LightboxAsset',
    'kartik\growl\GrowlAsset',
    'mcms\common\assets\TargetXssAsset',
    'mcms\common\assets\GettingPushAsset',
  ];
}
