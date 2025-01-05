<?php

namespace mcms\statistic\components\newStat;

use kartik\grid\BooleanColumn;
use mcms\common\grid\ActionColumn;
use yii\grid\SerialColumn;
use yii\helpers\ArrayHelper;

/**
 */
class ExportMenu extends \rgk\export\ExportMenu
{
  public $dataColumnClass = Column::class;
  public $exportFormView = '@mcms/common/grid/views/_form';
  public $exportColumnsView = '@mcms/common/grid/views/_columns';
  public $autoXlFormat = true;
  public $templateId;

  public function init()
  {
    parent::init();
    $this->onInitExcel = function ($excel, self $grid) {
      (new ExcelInfoSheetWriter($excel, $grid->statisticModel, $this->templateId, 1))->run();
      $this->_objSpreadsheet->setActiveSheetIndex(0); // <- возвращаем указатель на главный лист
    };
  }


  /**
   * Отличие от родительского в том, что там на значение накладывался форматтер. Тут убрал
   * @inheritdoc
   */
  public function generateRow($model, $key, $index)
  {
    /**
     * @var Column $column
     */
    $this->_endCol = 0;
    foreach ($this->getVisibleColumns() as $column) {
      if ($column instanceof SerialColumn) {
        $value = $column->renderDataCell($model, $key, $index);
      } elseif ($column instanceof BooleanColumn) {
        $value = $value = ArrayHelper::getValue($model, $column->attribute);
      } elseif ($column instanceof ActionColumn) {
        $value = '';
      } else {

        $value = ($column->content === null) ? (method_exists($column, 'getDataCellValue') ?
          $column->getDataCellValue($model, $key, $index) : // <--- вот где-то тут был форматтер в родительском
          $column->renderDataCell($model, $key, $index)) :
          call_user_func($column->content, $model, $key, $index, $column);

      }
      if (empty($value) && !empty($column->attribute) && $column->attribute !== null && $column->attribute !== 'groups') {
        $value = ArrayHelper::getValue($model, $column->attribute, 0); //  <--- CUSTOM FIX: иначе значения 0 не показывались
      }
      $this->_endCol++;
      $cellCoordinates = self::columnName($this->_endCol) . ($index + $this->_beginRow + 1);
      $format = $this->enableFormatter && isset($column->format) ? $column->format : 'raw';
      $formatName = is_array($format) ? $format[0] : $format;

      if ($column->excelFormat) {
        $formatName = $column->excelFormat;
      }


      $cell = $this->setExcelCellValue($cellCoordinates, $value, $formatName);

      $this->raiseEvent('onRenderDataCell', [$cell, $value, $model, $key, $index, $this]);
    }
  }
}
