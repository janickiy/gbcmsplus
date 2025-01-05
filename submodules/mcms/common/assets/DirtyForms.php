<?php

namespace mcms\common\assets;

use yii\web\AssetBundle;

class DirtyForms extends AssetBundle
{
    public $sourcePath = '@bower/jquery.dirtyforms/';

    public $css = [];

    public $js = [
        'jquery.dirtyforms.js',
    ];

    public $depends = [];
}
