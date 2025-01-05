<?php

namespace mcms\partners\assets\landings\wapcash;

use Yii;
use yii\web\AssetBundle;

class LandingAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/landings/wapcash';

  public $css = [
    'https://cdnjs.cloudflare.com/ajax/libs/uikit/2.24.3/css/uikit.min.css',
    'css/main.css',
  ];

  public $js = [
    'https://cdnjs.cloudflare.com/ajax/libs/uikit/2.24.3/js/uikit.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/uikit/2.24.3/js/components/slider.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/uikit/2.24.3/js/core/dropdown.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/uikit/2.24.3/js/core/modal.min.js',
    'js/main.js',
  ];

  public $depends = [
    'yii\web\JqueryAsset',
    'yii\web\YiiAsset',
    'mcms\common\assets\TargetXssAsset',
  ];

}
