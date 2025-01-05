<?php
/**
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var string $exportFileName
 * @var string $exportRequestParam
 * @var int $limit
 */

use rgk\export\ExportMenu;
use yii\helpers\Json;

$gridColumns = [
  'id',
  [
    'attribute' => 'type',
    'value' => function($model) {
      return $model->typeLabel;
    }
  ],
  [
    'attribute' => 'status',
    'value' => function($model) {
      return $model->statusLabel;
    }
  ],
  'errors',
  [
    'attribute' => 'status_code',
    'contentOptions' => ['style' => 'min-width: 100px']
  ],
  [
    'attribute' => 'url',
    'format' => 'raw',
  ],
  [
    'attribute' => 'time',
    'format' => 'datetime',
  ]
];
// костыль, чтобы затригерить загрузку файла в виджете ExportMenu
$exportMenu = new ExportMenu(['dataProvider' => $dataProvider]);
$_POST[$exportRequestParam] = true;
$_POST[$exportMenu->exportTypeParam] = ExportMenu::FORMAT_CSV;
$_POST[$exportMenu->colSelFlagParam] = true;
$_POST[$exportMenu->exportColsParam] = Json::encode(array_keys($gridColumns));

?>
<?=
ExportMenu::widget([
  'dataProvider' => $dataProvider,
  'columns' => $gridColumns,
  'filename' => $exportFileName,
  'exportRequestParam' => $exportRequestParam,
  'exportConfig' => [
    ExportMenu::FORMAT_CSV => [
      'mime' => 'application/csv',
      'extension' => 'csv',
      'writer' => ExportMenu::FORMAT_CSV,
    ],
    ExportMenu::FORMAT_EXCEL_X => false,
    ExportMenu::FORMAT_HTML => false,
    ExportMenu::FORMAT_PDF => false,
    ExportMenu::FORMAT_TEXT => false,
    ExportMenu::FORMAT_EXCEL => false,
  ],
]);
?>