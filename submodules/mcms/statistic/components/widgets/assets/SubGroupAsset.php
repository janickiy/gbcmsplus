<?php

namespace mcms\statistic\components\widgets\assets;

use yii\web\AssetBundle;

/**
 * Class SubGroupAsset
 * @package common\modules\statistic\widgets\assets
 */
class SubGroupAsset extends AssetBundle
{
  /**
   * @var string
   */
  public $sourcePath = __DIR__;

  /**
   * @var array
   */
  public $js = [
    'js/sub-group.js',
  ];
  /**
   * @var array
   */
  public $depends = [
    'yii\web\YiiAsset',
  ];
}