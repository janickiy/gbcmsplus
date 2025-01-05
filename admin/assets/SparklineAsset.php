<?php

namespace admin\assets;

use yii\web\AssetBundle;

class SparklineAsset extends AssetBundle
{
    public $sourcePath = '@admin/assets/resources/default';

    public $css = [
        'scss/common_stats.scss',
        'scss/overview.scss',
    ];

    public $js = [
        'js/jquery.sparkline.min.js'
    ];

    public $depends = [
        'admin\assets\DashboardAsset',
    ];
}