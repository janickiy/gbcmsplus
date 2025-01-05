<?php

namespace mcms\partners\assets\landings\affshark;

use Yii;
use yii\web\AssetBundle;

class LandingAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/landings/affshark';

  public $css = [
    'css/main.min.css',
  ];

  public $js = [
    'js/scripts.min.js',
  ];

  public $depends = [
    'yii\web\JqueryAsset',
    'yii\web\YiiAsset',
    'mcms\common\assets\TargetXssAsset',
  ];

}
