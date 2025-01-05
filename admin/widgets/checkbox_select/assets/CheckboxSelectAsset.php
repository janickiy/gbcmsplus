<?php

namespace admin\widgets\checkbox_select\assets;

use yii\web\AssetBundle;

class CheckboxSelectAsset extends AssetBundle
{
  public $sourcePath = __DIR__;

  public $js = [
    'js/checkbox-select.js',
  ];

  public $css = [
    'css/checkbox-select.css',
  ];

  public $depends = [
    'admin\assets\AppAsset'
  ];
}
