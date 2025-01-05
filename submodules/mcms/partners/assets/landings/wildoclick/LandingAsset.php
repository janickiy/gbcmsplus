<?php

namespace mcms\partners\assets\landings\wildoclick;

use Yii;
use yii\web\AssetBundle;

class LandingAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/landings/wildoclick';

  public $css = [
    'css/styles.css',
    'css/jquery.formstyler.css',
    'css/jquery.bxslider.css',
    'css/custom.css',
  ];
  public $js = [
    'js/jquery.bxslider.min.js',
    'js/jquery.inview.min.js',
    'js/jquery.formstyler.min.js',
    'js/scripts.js',
  ];
  public $depends = [
    'yii\web\JqueryAsset',
  ];

}
