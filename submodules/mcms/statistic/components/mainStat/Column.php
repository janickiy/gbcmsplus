<?php

namespace mcms\statistic\components\mainStat;

use kartik\grid\DataColumn;

/**
 * Класс колонки для нашего офигенного грида
 */
class Column extends DataColumn
{
  public $group;
  /** @var string формат названия колонки для подсказок. Можно использовать подстановки [group] и [label] */
  public static $hintColumnFormat = '([group]: [label])';
  /** @var string Текст подсказки у столбца. Можно записывать так:
   * {{count_hits}} - {{count_tb}}
   * Между скобками всё заменится на названия столбцов с такими $this->key или $this->attribute если ключ отсутствует.
   * Формат названия столбцов @see StatisticColumn::$hintColumnFormat
   */
  public $hint;
  /** @var array системные шаблоны, которым принадлежит колонка */
  public $template;

  /**
   * сделал публичным. Вообще не ясно зачем спрятали доступ к этому методу
   * @inheritdoc
   * @since 2.0.8
   */
  public function getHeaderCellLabel()
  {
    return parent::getHeaderCellLabel();
  }
}
