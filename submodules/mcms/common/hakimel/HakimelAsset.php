<?php

namespace mcms\common\hakimel;

use yii\web\AssetBundle;


/**
 * Class ActionColumnAsset
 * @package mcms\common\grid
 */
class HakimelAsset extends AssetBundle
{
    public $sourcePath = '@vendor/hakimel/Ladda/dist/';

    public $css = [
        'ladda-themeless.min.css'
    ];
    public $js = [
        'spin.min.js',
        'ladda.min.js',
        'ladda.jquery.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset'
    ];
}