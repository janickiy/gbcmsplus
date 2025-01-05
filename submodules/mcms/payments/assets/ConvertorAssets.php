<?php

namespace mcms\payments\assets;

use kartik\form\ActiveFormAsset;
use yii\web\AssetBundle;

class ConvertorAssets extends AssetBundle
{
  public $sourcePath = __DIR__ . '/resources';

  public $js = [
    'js/reseller-convertor.js',
  ];

  /**
   * @inheritdoc
   */
  public $depends = [
    'kartik\form\ActiveFormAsset',
    'yii\widgets\ActiveFormAsset',
  ];
}