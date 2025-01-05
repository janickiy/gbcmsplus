<?php

namespace mcms\common\grid;

use yii\web\AssetBundle;

class SortIcons extends AssetBundle
{
    public $sourcePath = __DIR__ . DIRECTORY_SEPARATOR . 'assets';
    public $css = [
        'css/sort-icons.css'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}