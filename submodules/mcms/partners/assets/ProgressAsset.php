<?php
namespace mcms\partners\assets;

use yii\web\AssetBundle;

class ProgressAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/resources/basic';
  public $css = [
  ];
  public $js = [
    'js/nprogress.js',
  ];
  public $depends = [
  ];
}