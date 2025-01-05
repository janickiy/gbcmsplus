<?php

namespace mcms\payments\assets;

use yii\web\AssetBundle;

class PaymentsGridAssets extends AssetBundle
{
  public $sourcePath = '@mcms/payments/assets/resources';
  public $css = [];
  public $js = [
    'js/payments-grid.js'
  ];
  public $depends = [
    'yii\web\JqueryAsset',
    'yii\web\YiiAsset',
  ];
}