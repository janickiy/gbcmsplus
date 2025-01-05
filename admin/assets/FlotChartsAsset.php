<?php

namespace admin\assets;

use yii\web\AssetBundle;

class FlotChartsAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        "js/flotchart/jquery.flot.cust.min.js",
        "js/flotchart/jquery.flot.resize.min.js",
        "js/flotchart/jquery.flot.fillbetween.min.js",
        "js/flotchart/jquery.flot.orderBar.min.js",
        "js/flotchart/jquery.flot.pie.min.js",
        "js/flotchart/jquery.flot.time.min.js",
        "js/flotchart/jquery.flot.tooltip.min.js",
    ];
    public $depends = [
        '\yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}