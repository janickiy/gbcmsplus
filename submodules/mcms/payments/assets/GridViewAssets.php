<?php

namespace mcms\payments\assets;

use yii\web\AssetBundle;

class GridViewAssets extends AssetBundle
{
  public $sourcePath = '@mcms/payments/assets/resources';
  public $css = ['css/grid.css'];
  public $js = [];
  public $depends = [
    'yii\web\YiiAsset',
  ];
}