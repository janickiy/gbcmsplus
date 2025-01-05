<?php

namespace mcms\common\assets;

use yii\web\AssetBundle;

class TargetXssAsset extends AssetBundle
{
    public $sourcePath = '@mcms/common/assets/resources';
    public $js = [
        'js/target-xss.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}