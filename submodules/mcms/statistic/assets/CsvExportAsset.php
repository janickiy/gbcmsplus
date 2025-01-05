<?php
namespace mcms\statistic\assets;

use yii\web\AssetBundle;

class CsvExportAsset extends AssetBundle
{
  public $sourcePath = '@mcms/statistic/assets/';
  public $css = [
  ];
  public $js = [
    'js/csv-export.js',
  ];
  public $depends = [
    'yii\web\YiiAsset',
  ];
}