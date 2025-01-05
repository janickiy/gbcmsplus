<?php
namespace mcms\statistic\assets;

use yii\web\AssetBundle;

/**
 * Ассет для модалки шаблонов столбцов
 */
class NewColumnsTemplateAsset extends AssetBundle
{
  public $sourcePath = '@mcms/statistic/assets/';

  public $js = [
    'js/new_stat/columns-template.js',
  ];
}