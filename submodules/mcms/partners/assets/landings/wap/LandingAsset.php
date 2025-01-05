<?php
namespace mcms\partners\assets\landings\wap;

use yii\web\AssetBundle;

class LandingAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/landings/wap/src';
  
  public $css = [
    'css/styles.css'
  ];

  public $js = [
    'js/swiper-bundle.min.js',
    'js/simplebar.min.js',
    'js/modernizr.js',
    'js/main.js',
    'js/forms.js'
  ];
  
  public $depends = [
    'yii\web\JqueryAsset',
  ];
}