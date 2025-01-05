<?php
namespace mcms\statistic\assets;

use Yii;
use yii\web\AssetBundle;
use yii\web\View;

class NewAdminStatisticAsset extends AssetBundle
{
  public function init()
  {
    $userRole = Yii::$app->user->identity->getRole()->one();

    Yii::$app->view->registerJs(/** @lang JavaScript */ "
    window.rgkUser = {role: '{$userRole->name}'};
    ", View::POS_HEAD);
  }

  public $sourcePath = '@mcms/statistic/assets/';
  public $css = [
    'css/new_stat/bootstrap-select.min.css',
   // 'css/new_stat/all.css',
    'css/new_stat/layout.css',
    'css/new_stat/statistics.css',
    'css/new_stat/filters.css',
    'css/new_stat/statistic-fixed-fields.css',
    'css/new_stat/styles.css',
  ];
  public $js = [
    'js/new_stat/bootstrap-select.min.js',
    'js/new_stat/main_admin_statistic.js',
  ];
  public $depends = [
    'yii\web\YiiAsset',
    'yii\bootstrap\BootstrapPluginAsset',
    'mcms\statistic\assets\CookiesAsset',
    'mcms\api\components\widgets\assets\ComplexFilterAsset',
    'admin\assets\AppAsset',
    'kartik\select2\Select2Asset',
    'kartik\date\DatePickerAsset',
    'kartik\base\WidgetAsset',
    'kartik\grid\GridViewAsset',
    'kartik\dialog\DialogBootstrapAsset',
    'kartik\form\ActiveFormAsset',
    'rgk\theme\smartadmin\assets\SmartAdminTabsAsset',
  ];
}