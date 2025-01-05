<?php

namespace mcms\notifications\components\assets;

use yii\web\AssetBundle;

class AdminAsset extends AssetBundle
{
  public $sourcePath = '@mcms/notifications/components/assets';

  public $js = [
    'js/form.js'
  ];

  public $depends = [
   '\yii\web\YiiAsset',
   '\yii\web\JqueryAsset',
  ];
}