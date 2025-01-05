<?php

namespace mcms\api\components\widgets\assets;

use yii\web\AssetBundle;

/**
 * Class ComplexFilterAsset
 * @package common\modules\api\widgets\assets
 */
class ComplexFilterAsset extends AssetBundle
{
  /**
   * @var string
   */
  public $sourcePath = __DIR__;

  /**
   * @var array
   */
  public $css = [
    'css/complex-filter.css',
  ];
  /**
   * @var array
   */
  public $js = [
    'js/complex-filter.js',
  ];
  /**
   * @var array
   */
  public $depends = [
    'yii\web\YiiAsset',
  ];
}