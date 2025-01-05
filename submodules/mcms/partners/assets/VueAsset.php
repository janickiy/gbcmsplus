<?php
namespace mcms\partners\assets;

use yii\web\AssetBundle;

class VueAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/resources/basic';
  public $css = [
  ];
  public $js = [
    'js/vue/vue.min.js',
    'js/vue/vue-focus.js',
  ];
  public $depends = [
  ];
}