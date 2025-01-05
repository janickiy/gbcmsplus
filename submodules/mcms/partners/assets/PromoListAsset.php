<?php

namespace mcms\partners\assets;

use Yii;
use yii\web\AssetBundle;

class PromoListAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/resources/basic';

  public $css = [];

  public $js = [
    'js/checkbox-filter.js',
    'js/pages/promo_list.js',
    'js/pages/links_list.js',
    'js/checkbox-select.js',
    'js/table_collapse.js',
  ];

  public $depends = [
    'mcms\partners\assets\ClipboardAsset',
    'mcms\partners\assets\PromoAsset',
    'mcms\partners\assets\BootstrapSelectAsset',
  ];



}
