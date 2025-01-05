<?php

namespace admin\assets;

use yii\web\AssetBundle;

class DashboardAsset extends AssetBundle
{
    public $sourcePath = '@admin/assets/resources/default';

    public $css = [
        'scss/main.scss',
        'scss/filters.scss',
    ];

    public $js = [
        'js/bootstrap-select.min.js',
        'js/moment.min.js',
        // Изменены методы
        // drawLegendBox L:10664
        // fillText L:10721
        // fit L:10517
        // draw L:10636
        'js/custom.Chart.min.js',
        'js/main.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'admin\assets\AppAsset',
        'admin\assets\CookiesAsset',
    ];
}