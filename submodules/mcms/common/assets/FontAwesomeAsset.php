<?php

namespace mcms\common\assets;

use yii\web\AssetBundle;

class FontAwesomeAsset extends AssetBundle
{
    public $sourcePath = '@vendor/fortawesome/font-awesome';
//  public $baseUrl = '@web';
    public $css = [
        'css/font-awesome.min.css',
    ];
}
