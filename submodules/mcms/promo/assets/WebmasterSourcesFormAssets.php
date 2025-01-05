<?php

namespace mcms\promo\assets;

use yii\web\AssetBundle;

class WebmasterSourcesFormAssets extends AssetBundle
{
  public $sourcePath = '@mcms/promo/assets/resources';
  public $js = ['js/webmaster-sources-form.js'];
  public $depends = [
    'yii\web\YiiAsset',
  ];
}