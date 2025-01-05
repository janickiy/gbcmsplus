<?php

namespace mcms\partners\assets\landings\wapbrothers;

use Yii;
use yii\web\AssetBundle;

class LandingAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/landings/wapbrothers';

  public $css = [
    'css/bootstrap-grid.min.css',
    'css/main.css',
    'css/fonts.css',
    'css/font-awesome.min.css',
  ];

  public $js = [
    'libs/modernizr/modernizr.js',
    'js/libs.min.js',
    'js/common.js'
  ];

  public $depends = [
    'yii\web\JqueryAsset',
    'yii\web\YiiAsset',
    'mcms\common\assets\TargetXssAsset',
  ];

}
