<?php

namespace mcms\promo\assets;

use yii\web\AssetBundle;

class WebmasterSourcesIndexAssets extends AssetBundle
{
  public $sourcePath = '@mcms/promo/assets/resources';
  public $css = ['css/webmaster-sources.css'];
  public $js = ['js/webmaster-sources-index.js'];
  public $depends = [
    'yii\web\YiiAsset',
  ];
}