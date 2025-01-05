<?php

namespace mcms\promo\components\widgets\assets;

use yii\web\AssetBundle;


class MainCurrenciesAsset extends AssetBundle
{

  public $sourcePath = __DIR__ ;

  public $css = [

  ];
  public $js = [
    'js/main_currencies.js'
  ];
  public $depends = [
    'yii\web\YiiAsset',
    'yii\bootstrap\BootstrapAsset',
  ];
}