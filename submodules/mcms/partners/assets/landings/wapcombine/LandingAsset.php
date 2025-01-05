<?php

namespace mcms\partners\assets\landings\wapcombine;

use Yii;
use yii\web\AssetBundle;

class LandingAsset extends AssetBundle
{
    public $sourcePath = '@mcms/partners/assets/landings/wapcombine';

    public $css = [
        'css/sign.min.css',
//        'css/jquery-ui.min.css',
        'css/jquery.formstyler.css',
    ];

    public $js = [
        'js/jquery.formstyler.min.js',
        'js/sign.js',
        'https://code.jquery.com/ui/1.12.0/jquery-ui.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
    ];

}
