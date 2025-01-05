<?php

namespace mcms\partners\assets\landings\wapconvert;

use Yii;
use yii\web\AssetBundle;

class LandingAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/landings/wapconvert';

  public $css = [
    'css/style.css',
  ];

  public $js = [
    'js/vendor/slick.min.js',
    'js/vendor/jquery.animateNumber.min.js',
    'js/vendor/jquery.arcticmodal.min.js',
    'js/vendor/jquery.viewportchecker.min.js',
    'js/scripts.min.js',
  ];

  public $depends = [
   'yii\web\JqueryAsset',
   'yii\web\YiiAsset',
  ];

}
