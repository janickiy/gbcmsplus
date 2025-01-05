<?php

namespace mcms\partners\assets\landings\wapadvert;

use Yii;
use yii\web\AssetBundle;

class LandingAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/landings/wapadvert';

  public $css = [
    'https://cdnjs.cloudflare.com/ajax/libs/uikit/2.24.3/css/uikit.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/uikit/2.24.3/css/components/tooltip.min.css',
    'css/main.css',
  ];

  public $js = [
    'https://cdnjs.cloudflare.com/ajax/libs/uikit/2.24.3/js/uikit.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/uikit/2.24.3/js/components/tooltip.min.js',
    'js/main.js',
  ];

  public $depends = [
    'yii\web\JqueryAsset',
    'yii\web\YiiAsset',
    'mcms\common\assets\TargetXssAsset',
  ];

}
