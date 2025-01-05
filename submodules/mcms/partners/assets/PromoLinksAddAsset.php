<?php

namespace mcms\partners\assets;

use Yii;
use yii\web\AssetBundle;

class PromoLinksAddAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/resources/basic';

  public $css = [
    'css/perfect-scrollbar.min.css',
  ];

  public $js = [
    'js/steps.js',
    'js/checkbox-select.js',
    'js/isotope.pkgd.min.js',
    'js/perfect-scrollbar.jquery.min.js',
    'js/pages/links_add.js',
  ];

  public $depends = [
    'mcms\partners\assets\PromoAsset',
    'mcms\partners\assets\BootstrapSelectAsset',
  ];



}
