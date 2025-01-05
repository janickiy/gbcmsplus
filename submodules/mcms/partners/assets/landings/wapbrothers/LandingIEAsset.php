<?php

namespace mcms\partners\assets\landings\wapbrothers;

use Yii;
use yii\web\AssetBundle;

class LandingIEAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/landings/wapbrothers';

  public $jsOptions = ['condition' => 'lt IE 9'];

  public $js = [
    'libs/html5shiv/es5-shim.min.js',
    'libs/html5shiv/html5shiv.min.js',
    'libs/html5shiv/html5shiv-printshiv.min.js',
    'libs/respond/respond.min.js',
  ];

  public $depends = [
    'yii\web\JqueryAsset',
    'yii\web\YiiAsset',
    'mcms\common\assets\TargetXssAsset',
  ];

}
