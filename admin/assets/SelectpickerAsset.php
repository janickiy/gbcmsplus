<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace admin\assets;

use yii\web\AssetBundle;

/**
 * SelectPicker
 */
class SelectpickerAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web/plugins/selectpicker';

    public $css = [
        'css/bootstrap-select.min.css',
    ];

    public $js = [
        'js/bootstrap-select.min.js',
    ];

    public $depends = [
        'admin\assets\AppAsset',
    ];
}