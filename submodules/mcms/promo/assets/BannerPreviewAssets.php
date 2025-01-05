<?php

namespace mcms\promo\assets;

use yii\web\AssetBundle;

class BannerPreviewAssets extends AssetBundle
{
  public $sourcePath = '@mcms/promo/assets/resources';
  public $js = ['js/banner-preview.js'];
  public $depends = [
    'yii\web\YiiAsset',
  ];
}