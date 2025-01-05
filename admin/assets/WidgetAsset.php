<?php

namespace admin\assets;

use yii\web\AssetBundle;

class WidgetAsset extends AssetBundle
{
    public $sourcePath = '@admin/assets/resources/default';

    public $css = [
        'scss/widget.scss',
    ];

    public $js = [

    ];

    public $depends = [
        'admin\assets\DashboardAsset',
    ];
}