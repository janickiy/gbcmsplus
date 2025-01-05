<?php

namespace mcms\partners\assets\basic;

use yii\web\AssetBundle;

class TicketsAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/resources/basic';

  public $css = [
    'scss/pages/tickets.scss',
  ];

  public $js = [
    'js/pages/tickets.js',
    'js/pages/tickets_dev.js',
  ];

  public $depends = [
    'mcms\partners\assets\BasicAsset',
    'mcms\partners\assets\BootstrapSelectAsset',
  ];
}
