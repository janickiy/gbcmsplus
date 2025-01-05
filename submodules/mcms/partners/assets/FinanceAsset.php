<?php

namespace mcms\partners\assets;

use yii\web\AssetBundle;

class FinanceAsset extends AssetBundle
{

  public $sourcePath = '@mcms/partners/assets/resources/basic';

  public $css = [
    'scss/pages/finance.scss'
  ];

  public $js = [
    'js/pages/finance.js'
  ];
  public $depends = [
    'mcms\partners\assets\BasicAsset',
    'mcms\partners\assets\BootstrapSelectAsset',
    'mcms\common\assets\CookiesAsset',
  ];
}