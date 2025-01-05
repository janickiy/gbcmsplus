<?php

namespace mcms\promo\assets;

use yii\web\AssetBundle;
use yii\web\View;

class ArbitrarySourcesViewAssets extends AssetBundle
{
  public $sourcePath = "@mcms/promo/assets/resources";
  public $css = ['css/arbitrary-sources.css'];
  public $depends = [
    'yii\web\YiiAsset',
  ];
}
