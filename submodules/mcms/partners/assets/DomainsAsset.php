<?php

namespace mcms\partners\assets;

use yii\web\AssetBundle;

class DomainsAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/resources/basic';

  public $css = [];

  public $js = [
    'js/pages/domains.js',
  ];

  public $depends = [
    'mcms\partners\assets\PromoAsset',
  ];
}
