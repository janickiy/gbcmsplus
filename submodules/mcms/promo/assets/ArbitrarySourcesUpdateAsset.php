<?php

namespace mcms\promo\assets;

use yii\web\AssetBundle;

class ArbitrarySourcesUpdateAsset extends AssetBundle
{

  public $sourcePath = "@mcms/promo/assets/resources";
  public $js = ['js/arbitrary-sources-update.js'];
  public $depends = [
    'yii\web\YiiAsset',
  ];
}
