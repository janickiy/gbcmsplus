<?php

namespace mcms\payments\assets;

use yii\web\AssetBundle;

class PaymentsResellerAssets extends AssetBundle
{
  public $sourcePath = '@mcms/payments/assets/resources';
  public $css = [];
  public $js = [
    'js/payments-convert.js',
    'js/payments-reseller.js',
  ];
  public $depends = [
    'yii\web\YiiAsset',
  ];
}