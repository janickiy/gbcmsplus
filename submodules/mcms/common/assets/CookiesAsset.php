<?php

namespace mcms\common\assets;

use yii\web\AssetBundle;

/**
 * Ассеты плагина JS cookie
 * @see https://github.com/js-cookie/js-cookie
 */
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