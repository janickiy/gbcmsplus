<?php

namespace mcms\partners\assets;

use Yii;
use yii\web\AssetBundle;

class BasicAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/resources/basic';

  public $css = [
    'https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=latin,cyrillic',
    'scss/layouts/main.scss',
  ];
  public $js = [
    'js/fm.scrollator.jquery.js',
    'js/x_main.js',
  ];
  public $depends = [
    'yii\web\YiiAsset',
    'yii\jui\JuiAsset',
    'yii\bootstrap\BootstrapPluginAsset',
    'branchonline\lightbox\LightboxAsset',
    'mcms\common\assets\TargetXssAsset',
    'mcms\common\assets\FontAwesomeAsset',
  ];
}
