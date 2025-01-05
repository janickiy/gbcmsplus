<?php

namespace admin\modules\credits\assets;

use yii\web\AssetBundle;

/**
 * Class CreditFormAsset
 * @package admin\modules\credits\assets
 */
class CreditFormAsset extends AssetBundle
{
    /** @inheritdoc */
    public $sourcePath = __DIR__;
    /** @inheritdoc */
    public $js = [
        'js/credit-form.js',
    ];
    /** @inheritdoc */
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
