<?php

namespace mcms\partners\assets;

use Yii;
use yii\web\AssetBundle;
use yii\web\View;
use kartik\daterange\MomentAsset;

class StatAsset extends AssetBundle
{
  public function init()
  {
    Yii::$app->view->registerJs('window.SETTING_AUTO_SUBMIT = ' . (int)Yii::$app->getModule('partners')->isAutoSubmitEnabled() . ';', View::POS_HEAD);
  }

  public $sourcePath = '@mcms/partners/assets/resources/basic';

  public $css = [
    'scss/pages/stats.scss',
    'css/fixedHeader.dataTables.css'
  ];

  public $js = [
    'js/jquery.dataTables.min.js',
    'js/dataTables.bootstrap.min.js',
    'js/dataTables.mobile.js',
    'js/checkbox-select.js',
    'js/pages/statistics.js',
    'js/checkbox-select.js',
    'js/dataTables.fixedHeader.min.js',
  ];
  public $depends = [
    'mcms\partners\assets\BasicAsset',
    'mcms\statistic\assets\CookiesAsset',
    'mcms\partners\assets\BootstrapSelectAsset',
    MomentAsset::class,
  ];



}
