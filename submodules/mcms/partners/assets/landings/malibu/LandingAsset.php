<?php

namespace mcms\partners\assets\landings\malibu;

use Yii;
use yii\web\AssetBundle;

class LandingAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/landings/malibu';

  public $css = [
    'css/all.min.css',
  ];

  public $js = [
    'js/all.min.js',
  ];

  public $cssOptions = [
    'type' => 'text/css',
  ];

  public $depends = [
    'mcms\common\assets\TargetXssAsset',
  ];
}
