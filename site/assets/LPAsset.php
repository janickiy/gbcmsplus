<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace site\assets;

use yii\web\AssetBundle;

class LPAsset extends AssetBundle
{

  public $sourcePath = '@site/assets/resources';

  public $js = [
    'main.js'
  ];

  public $depends = [
    'site\assets\AppAsset',
  ];
}
