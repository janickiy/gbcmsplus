<?php

namespace mcms\partners\assets;

use Yii;
use yii\web\AssetBundle;

class BootstrapSelectAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/resources/basic';

  public $js = [
    'js/bootstrap-select.js'
  ];
  public $depends = [
    'yii\web\YiiAsset',
    'yii\bootstrap\BootstrapPluginAsset'
  ];

  public function init()
  {
    array_push($this->js, 'js/i18n/bootstrap-select/bootstrap-select_' . Yii::$app->language . '.js');
    parent::init();
  }
}
