<?php

namespace mcms\promo\components\widgets;

class BannerDynamicFormAsset extends \yii\web\AssetBundle
{

  public $sourcePath = __DIR__ . '/resources';

  public $js = [
    'js/yii2-dynamic-form.js',
  ];

  /**
   * @inheritdoc
   */
  public $depends = [
    'yii\web\JqueryAsset',
    'yii\widgets\ActiveFormAsset'
  ];

}
