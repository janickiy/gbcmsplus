<?php

namespace mcms\statistic\components\newStat;
use Closure;
use mcms\common\helpers\ArrayHelper;
use Yii;
use yii\helpers\Html;

/**
 * Грид для рендера статы просто в виде списка строк <tr>...</tr><tr>....</tr>
 * Надо для запроса вложенных группировок
 */
class PartialGrid extends Grid
{

  public $showHeader = false;
  public $rowOptions = ['class' => 'tbody secondary-group-row'];
  public $collapseRowOptions = ['class' => 'collapse-row hideSubtable', 'data-code' => 'hide'];

  /**
   * Переопредилили, чтобы не добавлялись теги <table> и <div>
   * @return null|string|string[]
   */
  public function run()
  {
    return $this->renderTableBody();
  }

  /**
   * Переопределил, чтобы не добавлялись лишние теги типа <tbody>
   */
  public function renderTableBody()
  {
    $models = array_values($this->dataProvider->getModels());
    $keys = $this->dataProvider->getKeys();
    $rows = [];
    foreach ($models as $index => $model) {
      $key = $keys[$index];
      if ($this->beforeRow !== null) {
        $row = call_user_func($this->beforeRow, $model, $key, $index, $this);
        if (!empty($row)) {
          $rows[] = $row;
        }
      }

      $rows[] = $this->renderTableRow($model, $key, $index);

      if ($this->afterRow !== null) {
        $row = call_user_func($this->afterRow, $model, $key, $index, $this);
        if (!empty($row)) {
          $rows[] = $row;
        }
      }
    }

    $colspan = count($this->columns);

    $collapseRowOptions = $this->collapseRowOptions;
    $collapseRowOptions['class'] .= ' ' . $this->rowOptions['class'];
    $collapseRow = Html::tag(
      'tr',
      '<td>' . Html::a(Yii::_t('statistic.new_statistic_refactored.submenu_hide_label'), 'javascript:void(0);') . '</td><td colspan="' . ($colspan - 1) . '"></td>',
      $collapseRowOptions
    );

    if (empty($rows)) {
      $emptyRowOptions = [];
      if (!($this->rowOptions instanceof Closure)) {
        $emptyRowOptions = $this->rowOptions;
      }
      return Html::tag('tr', "<td colspan=\"$colspan\">" . $this->renderEmpty() . '</td>', $emptyRowOptions)
        . $collapseRow;
    }
    return implode("\n", $rows) . $collapseRow;
  }
}
