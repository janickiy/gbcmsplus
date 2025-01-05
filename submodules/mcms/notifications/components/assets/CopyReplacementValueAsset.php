<?php

namespace mcms\notifications\components\assets;

use yii\web\AssetBundle;

class CopyReplacementValueAsset extends AssetBundle
{
  public $sourcePath = '@mcms/notifications/components/assets';

  public $js = [
    'js/copy-replacement-value.js'
  ];

  public $depends = [
    '\yii\web\JqueryAsset',
  ];
}