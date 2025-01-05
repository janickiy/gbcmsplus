<?php

namespace mcms\promo\assets;

use yii\web\AssetBundle;

class RebillConditionsAsset extends AssetBundle
{
  public $sourcePath = '@mcms/promo/assets/resources';
  public $css = ['css/rebill-conditions.css'];
  public $depends = [
    'yii\web\YiiAsset',
  ];
}
