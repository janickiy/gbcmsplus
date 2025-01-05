<?php

namespace mcms\promo\assets;

use yii\web\AssetBundle;

class LandingsFormAsset extends AssetBundle
{
  public $sourcePath = '@mcms/promo/assets/resources';
  public $css = ['css/landingsForm.css'];
  public $js = [];
  public $depends = [
    'yii\web\YiiAsset',
  ];
}