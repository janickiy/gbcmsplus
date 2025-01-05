<?php

namespace mcms\partners\assets;

use Yii;
use yii\web\AssetBundle;

class PromoAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/resources/basic';

  public $css = [
    'scss/pages/promo.scss',
  ];

  public $js = [];
  public $depends = [
    'mcms\partners\assets\BasicAsset',
  ];



}
