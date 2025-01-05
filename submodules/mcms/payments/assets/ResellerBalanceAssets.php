<?php

namespace mcms\payments\assets;

use yii\web\AssetBundle;

/**
 * Class ResellerBalanceAssets
 * @package mcms\payments\assets
 */
class ResellerBalanceAssets extends AssetBundle
{
  public $sourcePath = '@mcms/payments/assets/resources';
  public $css = [
    'css/reseller_balance.css'
  ];
  public $js = [
    'js/payments-grid.js'
  ];
  public $depends = [
    'yii\web\JqueryAsset',
    'yii\web\YiiAsset',
  ];
}