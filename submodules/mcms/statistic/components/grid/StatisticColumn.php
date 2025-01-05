<?php

namespace mcms\statistic\components\grid;

use kartik\grid\DataColumn;

/**
 * Специальный класс для столбцов основной статы в админке
 */
class StatisticColumn extends DataColumn
{
  /** @var  string уникальный ключ столбца */
  public $key;
  public $groupType;
  /**
   * @var bool Показывать ли данный столбец в форме шаблонов столбцов.
   * Нужно иметь список всех возможных столбцов для данного юзера из-за фильтра по revshare/cpa.
   * Нужно чтобы не было ошибки, что visibleInTemplate неизвестное свойство.
   * Потому что инициальзация Yii GridView идет до инициализации StatisticGrid в ExportMenu
   */
  public $visibleInTemplate;
  /** @var string[]|string Список системных шаблонов, к которым принадлежит этот столбец */
  public $templates;
  /** @var string Текст подсказки у столбца. Можно записывать так:
   * {{count_hits}} - {{count_tb}}
   * Между скобками всё заменится на названия столбцов с такими $this->key или $this->attribute если ключ отсутствует.
   * Формат названия столбцов @see StatisticColumn::$hintColumnFormat
   */
  public $hint;
  /** @var string формат названия колонки для подсказок. Можно использовать подстановки [group] и [label] */
  public $hintColumnFormat = '([group]: [label])';
  /** @var  MainAdminStatisticGrid переопределил для пхпдока */
  public $grid;

  /**
   * @var string[] массив-кэш для @see self::getKeyIndexedLabels()
   */
  private static $keyIndexedLabels;

  public function init()
  {
    $this->label = $this->label ? : $this->grid->statisticModel->getGridColumnLabel($this->attribute);
    parent::init();
  }

  /**
   * Расширили чтобы указывать подсказки к столбцам
   * @return string
   */
  public function renderHeaderCell()
  {
    if ($this->hint && !isset($this->headerOptions['title'])) {
      $this->headerOptions['title'] = strtr($this->hint, $this->getKeyIndexedLabels());
    }
    return parent::renderHeaderCell();
  }

  /**
   * Массив в виде ['{{count_hits}}' => '(Трафик: Клики)', '{{count_tb}}' => '(Трафик: ТБ)']
   * В виде значение отформатированное название столбца по шаблону @see StatisticColumn::$hintColumnFormat
   * @return array
   */
  protected function getKeyIndexedLabels()
  {
    if (self::$keyIndexedLabels !== null) {
      return self::$keyIndexedLabels;
    }

    self::$keyIndexedLabels = [];

    foreach ($this->grid->allColumns as $column) {
      /** @var StatisticColumn $column */
      $index = $column->key ?: $column->attribute;

      self::$keyIndexedLabels['{{' . $index . '}}'] = strtr(
        $this->hintColumnFormat,
        [
          '[group]' => MainAdminStatisticGrid::getHeaderGroups($column->groupType),
          '[label]' => $column->label,
        ]
      );
    }

    return self::$keyIndexedLabels;
  }
}
