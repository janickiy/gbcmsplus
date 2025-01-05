<?php

namespace mcms\partners\assets;

use Yii;
use yii\web\AssetBundle;

class PromoLinksListAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/resources/basic';

  public $css = [];

  public $js = [
    'js/pages/links_list.js',
    'js/checkbox-select.js',
    'js/table_collapse.js',
  ];

  public $depends = [
    'mcms\partners\assets\PromoListAsset',
  ];



}
