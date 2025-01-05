<?php

/**
 * @package   yii2-grid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014 - 2016
 * @version   3.1.1
 */

namespace mcms\common\grid;

use mcms\common\grid\assets\GridViewAsset;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use kartik\base\Config;

/**
 * Class GridView переопределяет класс картика GridView, и подключает измененный js-файл yii.gridView.js
 * Изменения gridView.js на 98 строке, изменена регулярка с /\[\]$/ на /\[\d*\]$/.
 * Без этого изменения не корректно работал фильтр GridView с множественным выбором (Select2).
 * Что не работало: Если выбрать несколько вариантов, затем отсортировать таблицу и после сортировки убрать один из
 * выбранных ранее вариантов, то ответ был все равно с удаленным вариантом, т.е. не удалялся.
 */
class GridView extends \kartik\grid\GridView
{
    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function run()
    {
        $this->initToggleData();
        $this->initExport();
        if ($this->export !== false && isset($this->exportConfig[self::PDF])) {
            Config::checkDependency(
                'mpdf\Pdf',
                'yii2-mpdf',
                "for PDF export functionality. To include PDF export, follow the install steps below. If you do not " .
                "need PDF export functionality, do not include 'PDF' as a format in the 'export' property. You can " .
                "otherwise set 'export' to 'false' to disable all export functionality"
            );
        }
        $this->initHeader();
        $this->initBootstrapStyle();
        $this->containerOptions['id'] = $this->options['id'] . '-container';
        Html::addCssClass($this->containerOptions, 'kv-grid-container');
        $this->registerAssets();
        $this->renderPanel();
        $this->initLayout();
        $this->beginPjax();

        $id = $this->options['id'];
        $options = Json::htmlEncode($this->getClientOptions());
        $view = $this->getView();
        GridViewAsset::register($view);
        $view->registerJs("jQuery('#$id').yiiGridView($options);");

        if ($this->showOnEmpty || $this->dataProvider->getCount() > 0) {
            $content = preg_replace_callback("/{\\w+}/", function ($matches) {
                $content = $this->renderSection($matches[0]);

                return $content === false ? $matches[0] : $content;
            }, $this->layout);
        } else {
            $content = $this->renderEmpty();
        }

        $options = $this->options;
        $tag = ArrayHelper::remove($options, 'tag', 'div');
        echo Html::tag($tag, $content, $options);

        $this->endPjax();
    }
}
