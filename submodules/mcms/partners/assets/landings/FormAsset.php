<?php

namespace mcms\partners\assets\landings;

use Yii;
use yii\web\AssetBundle;

class FormAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/landings';

  public $js = [
    'js/form.js',
  ];
  public $depends = [
    'yii\web\JqueryAsset',
    'mcms\common\assets\GettingPushAsset',
  ];

}
