<?php

namespace mcms\statistic\components\newStat;

use kartik\grid\DataColumn;
use mcms\statistic\components\newStat\mysql\Row;
use Yii;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;

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
  /** @inheritdoc */
  public $encodeLabel = false;

  /** @var integer|float сумма значений основного аттрибута */
  public $sum;
  /** @var integer|float сумма значений доп аттрибута */
  public $addSum;
  /** @var integer|float среднее значение аттрибута */
  public $avg;
  /** @var integer|float среднее значение доп аттрибута */
  public $addAvg;

  /** @var array опции заголовка столбца */
  public $headerLabelOptions = [];
  /** @var string дополнительный аттрибут для колонки */
  public $addAttribute;
  /** @var string дополнительное значение для колонки */
  public $addValue;
  /** @var string формат дополнительного аттибута */
  public $addFormat;
  /** @var string|\Closure поповер для значения колоки */
  public $popover;
  /** @var bool скрыть значение доп аттрибута для строки тотал */
  public $hideTotalAdd;
  /** @var bool является ли колонка группировкой */
  public $isGroupColumn;
  /**
   * @var string кастомный формат для Excel выгрузки. Например даты на вьюхе выгружаем в raw, а в эксель в date
   */
  public $excelFormat;

  public function init()
  {
    $this->contentOptions = function (Row $model, $key, $index, Column $column) {
      $options = [
        'class' => $column->getColumnIsSorted() ? 'sorted' : [],
        'data-code' => $column->attribute
      ];
      // Если это колонка группировки, добавляем соответствующий класс и прописываем значение группировки
      if ($this->isGroupColumn) {
        $groups = $model->getGroups();

        Html::addCssClass($options, 'groupCell');
        $options['data-value'] = reset($groups)->getFilterBy();
      }

      return $options;
    };
    parent::init();
  }



  /**
   * сделал публичным. Вообще не ясно зачем спрятали доступ к этому методу
   * @inheritdoc
   * @since 2.0.8
   */
  public function getHeaderCellLabel()
  {
    // label заполняется в колонке группировки
    if ($this->label !== null) {
      return $this->label;
    }

    $mainHeader = Column::getMainAttributeSpan(Yii::_t('new_statistic_refactored.' . $this->attribute));
    $addHeader = $this->addAttribute
      ? '<br>' . Column::getAddAttributeSpan('(' . Yii::_t('new_statistic_refactored.' . $this->addAttribute) . ')')
      : '';

    return $mainHeader . $addHeader;
  }

  /**
   * Переопределен метод т.к. навесить popover на th не получается
   * Шапку колонки оборачиваем в span и навешиваем на него popover который указан в headerLabelOptions
   * @inheritdoc
   */
  protected function renderHeaderCellContent()
  {
    if ($this->header !== null || ($this->label === null && $this->attribute === null)) {
      return parent::renderHeaderCellContent();
    }

    $label = $this->getHeaderCellLabel();
    if ($this->encodeLabel) {
      $label = Html::encode($label);
    }

    if ($this->attribute !== null && $this->enableSorting &&
      ($sort = $this->grid->dataProvider->getSort()) !== false && $sort->hasAttribute($this->attribute)) {
      return $sort->link($this->attribute, array_merge($this->sortLinkOptions, $this->headerLabelOptions, ['label' => $label]));
    }
    return Html::tag('span', $label, $this->headerLabelOptions);
  }


  /**
   * Отсортирован ли столбец
   * @return bool
   */
  public function getColumnIsSorted()
  {
    if ($this->grid->dataProvider->getSort()) {
      return array_key_exists($this->attribute, $this->grid->dataProvider->getSort()->getAttributeOrders());
    }

    return false;
  }

  /**
   * Возвращает значение аттрибута колонки
   * @param mixed $model the data model
   * @param mixed $key the key associated with the data model
   * @param int $index the zero-based index of the data model among the models array returned by [[GridView::dataProvider]].
   * @return string the data cell value
   */
  public function getDataCellValue($model, $key, $index)
  {
    $value = ArrayHelper::getValue($model, $this->attribute);
    if ($this->value !== null) {
      return call_user_func($this->value, $model, $key, $index, $this);
    }

    $popoverOptions = [];
    if ($this->popover !== null) {
      $popoverContent = is_string($this->popover)
        ? ArrayHelper::getValue($model, $this->popover)
        : call_user_func($this->popover, $model, $key, $index, $this);
      $popoverOptions = [
        'rel' => 'popover-hover',
        'data-placement' => 'top',
        'data-content' => $popoverContent,
        'data-html' => 'true',
        'data-toggle' => 'popover',
        'data-trigger' => 'hover',
      ];
    }

    return Column::getMainAttributeSpan($this->grid->formatter->format($value, $this->format), $popoverOptions);
  }

  /**
   * Возвращает значение дополнительного аттрибута колонки
   * @param mixed $model the data model
   * @param mixed $key the key associated with the data model
   * @param int $index the zero-based index of the data model among the models array returned by [[GridView::dataProvider]].
   * @return string the data cell value
   */
  public function getDataCellAddValue($model, $key, $index)
  {
    $addValue = ArrayHelper::getValue($model, $this->addAttribute);
    if ($this->addValue !== null) {
      return call_user_func($this->addValue, $model, $key, $index, $this);
    }
    return Column::getAddAttributeSpan($this->grid->formatter->format($addValue, $this->addFormat));
  }

  /**
   * @inheritdoc
   */
  protected function renderDataCellContent($model, $key, $index)
  {
    if ($this->content === null) {
      return $this->getDataCellValue($model, $key, $index) .
        ($this->addAttribute ? $this->getDataCellAddValue($model, $key, $index) : '');
    }

    return parent::renderDataCellContent($model, $key, $index);
  }

  /**
   * Скрыть значение доп аттрибута в строке тотал
   * @return bool
   */
  public function isHideTotalAdd()
  {
    return $this->hideTotalAdd === true;
  }

  /**
   * @param $content
   * @param $options array
   * @return string
   */
  public static function getMainAttributeSpan($content, $options = [])
  {
    return Html::tag(
      'span',
      $content,
      ArrayHelper::merge(['class' => 'celMain'], $options)
    );
  }

  /**
   * @param $content string
   * @param $options array
   * @return string
   */
  public static function getAddAttributeSpan($content, $options = [])
  {
    return Html::tag(
      'span',
      $content,
      ArrayHelper::merge(['class' => 'celAdd'], $options)
    );
  }

  /**
   * Формат для среднего
   * Если установлен int, меняю на decimal
   * @return array|string
   */
  public function getAvgFormat()
  {
    return $this->intToDecimal($this->format);
  }

  /**
   * Формат для среднего (дополнительное значение)
   * Если установлен int, меняю на decimal
   * @return array|string
   */
  public function getAvgAddFormat()
  {
    return $this->intToDecimal($this->addFormat);
  }

  /**
   * @param $format
   * @return array|string
   */
  public function intToDecimal($format)
  {
    if ($format === 'integer') {
      $format = 'magicDecimals';
    }
    return $format;
  }
}
