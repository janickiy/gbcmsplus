<?php

namespace admin\assets;

use yii\web\AssetBundle;

class CookiesAsset extends AssetBundle
{
    public $sourcePath = '@bower/js-cookie/src/';
    public $css = [
    ];
    public $js = [
        'js.cookie.js',
    ];
    public $depends = [
    ];
}