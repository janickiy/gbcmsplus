<?php

namespace mcms\partners\assets\landings\wapclick;

use Yii;
use yii\web\AssetBundle;

class LandingAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/landings/wapclick';

  public $css = [
    'https://fonts.googleapis.com/css?family=Roboto:400,300,500,700&subset=latin,cyrillic',
    'css/all.min.css',
  ];

  public $js = [
    'js/dropdown.js',
    'js/fancySelect.js',
    'js/jquery.viewportchecker.js',
    'js/modal.js',
    'js/transition.js',
    'js/tooltip.js',
    'js/x_main.js',
  ];

  public $depends = [
    'yii\web\JqueryAsset',
    'yii\web\YiiAsset',
    'mcms\common\assets\TargetXssAsset',
  ];

}
