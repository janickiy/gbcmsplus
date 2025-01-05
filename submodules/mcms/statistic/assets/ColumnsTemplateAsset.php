<?php
namespace mcms\statistic\assets;

use yii\web\AssetBundle;

/**
 * Ассет для модалки шаблонов столбцов
 */
class ColumnsTemplateAsset extends AssetBundle
{
  public $sourcePath = '@mcms/statistic/assets/';

  public $js = [
    'js/columns-template.js',
  ];
}