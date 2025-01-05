<?php

namespace mcms\promo\assets;

use yii\web\AssetBundle;
use yii\web\View;

class ArbitrarySourcesIndexAssets extends AssetBundle
{
  public $sourcePath = "@mcms/promo/assets/resources";
  public $css = ['css/arbitrary-sources.css'];
  public $js = ['js/arbitrary-sources-index.js'];
  public $depends = [
    'yii\web\YiiAsset',
  ];
}
