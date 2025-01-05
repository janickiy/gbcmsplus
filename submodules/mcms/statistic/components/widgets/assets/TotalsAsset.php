<?php

namespace mcms\statistic\components\widgets\assets;

use yii\web\AssetBundle;

/**
 * Class TotalsAsset
 * @package common\modules\statistic\widgets\assets
 */
class TotalsAsset extends AssetBundle
{
  /**
   * @var string
   */
  public $sourcePath = __DIR__;

  /**
   * @var array
   */
  public $css = [
    'scss/total-debt.scss',
    'scss/total-awaiting-payments.scss',
  ];
  /**
   * @var array
   */
  public $depends = [
    'yii\web\YiiAsset',
  ];
}