<?php

namespace mcms\common\assets;

use yii\web\AssetBundle;

class ErrorAsset extends AssetBundle
{
    public $sourcePath = '@mcms/common/assets/resources';

    public $css = [
        'https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=latin,cyrillic',
        'scss/pages/error.scss',
    ];

    public $js = [
    ];

    public $depends = [
    ];
}
