<?php

namespace mcms\common\assets;

use yii\web\AssetBundle;

class GettingPushAsset extends AssetBundle
{
    public $sourcePath = '@mcms/common/assets/resources';
    public $js = [
        'https://www.gstatic.com/firebasejs/3.6.8/firebase.js',
        'js/firebase_getting.js',
    ];
}