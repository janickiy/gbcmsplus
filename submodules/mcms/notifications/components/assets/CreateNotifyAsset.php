<?php

namespace mcms\notifications\components\assets;

use yii\web\AssetBundle;

class CreateNotifyAsset extends AssetBundle
{
  public $sourcePath = '@mcms/notifications/components/assets';

  public $js = [
  ];

  public $depends = [
    '\yii\web\JqueryAsset',
  ];

}