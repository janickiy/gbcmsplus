<?php

namespace mcms\pages\components\widgets;

class DynamicFormAsset extends \yii\web\AssetBundle
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
