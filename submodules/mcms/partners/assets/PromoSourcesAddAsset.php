<?php

namespace mcms\partners\assets;

use Yii;
use yii\web\AssetBundle;

class PromoSourcesAddAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/resources/basic';

  public $css = [];

  public $js = [
    'js/steps.js',
    'js/checkbox-filter.js',
    'js/pages/sources_add.js',
  ];

  public $depends = [
    'mcms\partners\assets\PromoAsset',
    'mcms\partners\assets\BootstrapSelectAsset',
  ];



}
