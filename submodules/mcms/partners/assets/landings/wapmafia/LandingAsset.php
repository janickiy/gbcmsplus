<?php

namespace mcms\partners\assets\landings\wapmafia;

use Yii;
use yii\web\AssetBundle;

class LandingAsset extends AssetBundle
{
    public $sourcePath = '@mcms/partners/assets/landings/wapmafia';

    public $css = [
        'https://fonts.googleapis.com/css?family=PT+Sans:400,700&amp;subset=latin,cyrillic',
        'css/style.css',
    ];

    public $js = [
        'js/custom.js',
    ];

    public $depends = [
        'yii\bootstrap\BootstrapPluginAsset',
        'yii\web\YiiAsset',
    ];

}
