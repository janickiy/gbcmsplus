<?php

namespace mcms\partners\assets\basic;

use yii\web\AssetBundle;

class NotificationAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/resources/basic';

  public $css = [
    'scss/pages/news.scss',
  ];

  public $js = [
    'js/pages/news.js',
  ];

  public $depends = [
    'yii\web\YiiAsset',
    'yii\bootstrap\BootstrapAsset',
    'mcms\partners\assets\BootstrapSelectAsset',
  ];
}
