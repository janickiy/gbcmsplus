<?php
namespace mcms\partners\assets;

use yii\web\AssetBundle;

class NotifierAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/resources/basic';
  public $css = [
  ];
  public $js = [
    'js/bootstrap-notify.js',
  ];
  public $depends = [
  ];
}