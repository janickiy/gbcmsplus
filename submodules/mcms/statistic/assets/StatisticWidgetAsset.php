<?php
namespace mcms\statistic\assets;

use Yii;
use yii\web\AssetBundle;
use yii\web\View;

/**
 * Class StatisticWidgetAsset
 * @package mcms\statistic\assets
 */
class StatisticWidgetAsset extends AssetBundle
{
  public function init()
  {
    Yii::$app->view->registerJs('window.SETTING_AUTO_SUBMIT = ' . (int)Yii::$app->getModule('statistic')->isAutoSubmitEnabled() . ';', View::POS_HEAD);
  }
  
  public $sourcePath = '@mcms/statistic/assets/';
  public $css = [
    'css/dataTables.bootstrap.min.css',
    'css/bootstrap-select.min.css',
    'css/dataTables.custom.css',
    'css/styles.css',
  ];
  public $js = [
    'js/bootstrap-select.min.js',
    'js/jquery.dataTables.min.js',
    'js/dataTables.bootstrap.min.js',
    'js/statistic.js',
  ];
  public $depends = [
    'yii\web\YiiAsset',
    'yii\bootstrap\BootstrapPluginAsset',
    'mcms\statistic\assets\CookiesAsset',
  ];
}