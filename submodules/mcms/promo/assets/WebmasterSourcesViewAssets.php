<?php

namespace mcms\promo\assets;

use yii\web\AssetBundle;

class WebmasterSourcesViewAssets extends AssetBundle
{
  public $sourcePath = '@mcms/promo/assets/resources';
  public $css = ['css/webmaster-sources.css'];
  public $depends = [
    'yii\web\YiiAsset',
  ];
}