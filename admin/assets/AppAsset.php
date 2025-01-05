<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace admin\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/bootstrap.min.css',
        'css/smartadmin-production-plugins.min.css',
        'css/smartadmin-production.min.css',
        'css/smartadmin-skins.min.css',
        'css/demo.min.css',
        'css/your_style.css',
        'https://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,300,400,700',
        'css/kostyl.css',
        'scss/custom-smartadmin-skin.scss',
    ];

    public $js = [
        'js/app.config.js',
        'js/pjax-defaults.js',
        'js/smartwidgets/jarvis.widget.min.js',
        'js/app.js',
        'js/common.js',
    ];

    public $depends = [
        'yii\bootstrap\BootstrapPluginAsset',
        'yii\jui\JuiAsset',
//    'yii\bootstrap\BootstrapAsset',
        'mcms\common\assets\FontAwesomeAsset',
        'mcms\common\assets\TargetXssAsset',
        'admin\assets\ConfirmAsset',
        'admin\assets\CookiesAsset',
        'mcms\common\assets\GettingPushAsset',
    ];
}