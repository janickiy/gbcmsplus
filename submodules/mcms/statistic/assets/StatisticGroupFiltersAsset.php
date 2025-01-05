<?php
namespace mcms\statistic\assets;

use yii\web\AssetBundle;

class StatisticGroupFiltersAsset extends AssetBundle
{
  public $sourcePath = '@mcms/statistic/assets/';
  
  public $js = [
    'js/statistic-group-filters.js',
  ];
  
  public $depends = [
    'yii\web\YiiAsset',
    'yii\bootstrap\BootstrapPluginAsset',
    'mcms\statistic\assets\CookiesAsset',
    'mcms\statistic\assets\StatisticAsset',
  ];
}