<?php

namespace mcms\notifications\components\assets;

use yii\web\AssetBundle;

class PushAsset extends AssetBundle
{
  public $sourcePath = '@mcms/notifications/components/assets';

  public $js = [
    'js/firebase_subscribe.js'
  ];

  public $depends = [
    'mcms\common\assets\GettingPushAsset',
  ];
}