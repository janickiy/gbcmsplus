<?php
use rgk\export\ExportMenu;
use mcms\statistic\components\mainStat\DataProvider;

/** @var DataProvider $dataProvider */
/** @var string $exportFileName */
/** @var string $exportWidgetId  */
/** @var array $gridColumns */
?>


<div id="export" class="statistics_collapsed">
  <div class="row">
    <div class="col-xs-12 export">
      <span><?= Yii::_t('statistic.select_export_format') ?>: </span>
      <?= ExportMenu::widget([
        'id' => $exportWidgetId,
        'clearBuffers' => true,
        'dataProvider' => $dataProvider,
        'filterFormId' => 'statistic-filter-form',
        'isPartners' => true,
        'columns' => array_map(function ($n) {
          if (isset($n['label'])) {
            $n['label'] = str_replace('<i class="icon-euro"></i>', '€', $n['label']);
            $n['label'] = str_replace('<i class="icon-ruble"></i>', '₽', $n['label']);
          }
          return $n;
        }, $gridColumns),
        'asDropdown' => false,
        'showConfirmAlert' => false,
        'target' => ExportMenu::TARGET_SELF,
        'filename' => $exportFileName,
        'exportConfig' => [
          ExportMenu::FORMAT_CSV => [
            'label' => Yii::_t('statistic.csv_text_format'),
            'icon' => 'icon-csv',
            'iconOptions' => ['class' => 'text-primary'],
            'linkOptions' => [''],
            'options' => ['class' => 'export-formate'],
            'mime' => 'application/csv',
            'extension' => 'csv',
            'writer' => ExportMenu::FORMAT_CSV,
          ],
          ExportMenu::FORMAT_EXCEL_X => [
            'label' => Yii::_t('statistic.document_excel'),
            'icon' => 'icon-xls',
            'linkOptions' => [],
            'options' => ['class' => 'export-formate'],
            'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'extension' => 'xlsx',
            'writer' => ExportMenu::FORMAT_EXCEL_X,
          ],
          ExportMenu::FORMAT_HTML => false,
          ExportMenu::FORMAT_PDF => false,
          ExportMenu::FORMAT_TEXT => false,
          ExportMenu::FORMAT_EXCEL => false,
        ]
      ]);
      ?>
    </div>
  </div>
  <div class="export-bottom">
    <span><i class="icon-danger"></i> <?= Yii::_t('statistic.use_table_filter') ?></span>
  </div>
</div>