<?php
namespace mcms\statistic\assets;

use Yii;
use yii\web\AssetBundle;
use yii\web\View;

/**
 * Class StatisticAsset
 * @package mcms\statistic\assets
 */
class MainAdminStatisticAsset extends AssetBundle
{
  public function init()
  {
    $userRole = Yii::$app->user->identity->getRole()->one();
    $autoSubmit = (int)Yii::$app->getModule('statistic')->isAutoSubmitEnabled();

    Yii::$app->view->registerJs(/** @lang JavaScript */ "
    window.SETTING_AUTO_SUBMIT = $autoSubmit;
    window.rgkUser = {role: '{$userRole->name}'};
    ", View::POS_HEAD);
  }

  public $sourcePath = '@mcms/statistic/assets/';
  public $css = [
    'css/dataTables.bootstrap.min.css',
    'css/bootstrap-select.min.css',
    'css/dataTables.custom.css',
    'css/styles.css',
    'css/statistic-fixed-fields.css',
  ];
  public $js = [
    'js/bootstrap-select.min.js',
    'js/jquery.dataTables.min.js',
    'js/dataTables.bootstrap.min.js',
    'js/natural.js',
    'js/dataTables.fixedColumns.min.js',
    'js/main_admin_statistic.js',
  ];
  public $depends = [
    'yii\web\YiiAsset',
    'yii\bootstrap\BootstrapPluginAsset',
    'mcms\statistic\assets\CookiesAsset',
  ];
}