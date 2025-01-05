<?php

namespace mcms\payments\assets;

use yii\web\AssetBundle;

class MultipleCurrencyAssets extends AssetBundle
{
  public $sourcePath = '@mcms/payments/assets/resources';
  public $css = [];
  public $js = [
    'js/multiple-currencies.js'
  ];
  public $depends = [
    'yii\web\JqueryAsset',
    'yii\web\YiiAsset',
  ];
}