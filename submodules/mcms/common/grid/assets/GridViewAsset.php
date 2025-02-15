<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace mcms\common\grid\assets;

use yii\web\AssetBundle;

/**
 * This asset bundle provides the javascript files for the [[GridView]] widget.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class GridViewAsset extends AssetBundle
{
  public $sourcePath = '@mcms/common/grid/assets';
  public $js = [
    'js/yii.gridView.js',
  ];
  public $depends = [
    'yii\web\YiiAsset',
  ];
}
