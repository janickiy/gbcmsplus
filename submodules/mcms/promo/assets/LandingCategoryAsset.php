<?php

namespace mcms\promo\assets;

use yii\web\AssetBundle;

class LandingCategoryAsset extends AssetBundle
{

  public $sourcePath = "@mcms/promo/assets/resources";
  public $js = ['js/landing-category.js'];
  public $depends = [
    'yii\web\YiiAsset',
  ];

}
