<?php

namespace mcms\partners\assets;

use yii\web\AssetBundle;

class FaqAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/resources/basic';

  public $css = [
    'scss/pages/faq.scss',
  ];
  public $js = [
    'js/pages/faq.js',
  ];
  public $depends = [
    'yii\web\YiiAsset',
    'yii\bootstrap\BootstrapAsset',
    'mcms\partners\assets\ProgressAsset'
  ];
}
