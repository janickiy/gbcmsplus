<?php


namespace mcms\common\grid;

use yii\web\AssetBundle;

/**
 * Class ActionColumnAsset
 * @package mcms\common\grid
 */
class ActionColumnAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . DIRECTORY_SEPARATOR . 'assets';

    public $css = [

    ];
    public $js = [
        /* TRICKY Ассет js/grid-modals.js удален, так как все модальные окна заменены на \mcms\common\widget\modal\Modal
         * Данный скрипт конфликтовал с новой системой модалок при использовании \mcms\common\widget\Editable ,
         * например при редактировании цены выкупа в лендингах (админка) */
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];
}