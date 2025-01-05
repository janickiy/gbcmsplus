<?php

namespace mcms\promo\assets;

use yii\web\AssetBundle;

class LandingsViewAsset extends AssetBundle
{
  public $sourcePath = '@mcms/promo/assets/resources';
  public $css = ['css/landingsView.css'];
  public $js = [];
  public $depends = [
    'yii\web\YiiAsset',
  ];
}