<?php

namespace mcms\user\assets;

use yii\web\AssetBundle;

class ProfileAsset extends AssetBundle
{
  public $sourcePath = '@mcms/payments/assets/resources';
  public $css = ['css/profile.css'];
  public $js = ['js/profile.js'];
  public $depends = [
    'yii\web\YiiAsset',
  ];
}