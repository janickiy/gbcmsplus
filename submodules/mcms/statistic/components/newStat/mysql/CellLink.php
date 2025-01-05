<?php

namespace mcms\statistic\components\newStat\mysql;

use mcms\statistic\components\newStat\FormModel;
use mcms\statistic\components\newStat\Group;
use mcms\statistic\components\newStat\mysql\groupFormats\MonthNumbers;
use mcms\statistic\components\newStat\mysql\groupFormats\WeekNumbers;
use mcms\statistic\models\Complain;
use Yii;

/**
 * Создаем ссылку в ячейке статы
 */
abstract class CellLink
{

  public $searchModelClass = FormModel::class;

  /**
   * @var Row
   */
  protected $row;
  private static $searchModel;

  /**
   * @see create()
   * @param Row $row
   * @see Complain::$type
   */
  protected function __construct(Row $row)
  {
    $this->row = $row;
  }

  /**
   * @param $count
   * @return string
   */
  abstract protected function toStringInternal($count);

  /**
   * @return string
   */
  final public function toString()
  {
    $count = $this->getCellValue();

    if (!Yii::$app->request instanceof \yii\web\Request) {
      return $count;
    }

    if ($count === 0) {
      return '0'; // уже и не вспомнить почему именно строку надо вернуть
    }

    return $this->toStringInternal($count);
  }

  /**
   * @return float|int|string
   */
  abstract protected function getCellValue();

  /**
   * Основная модель фильтрации
   * TRICKY мы не смогли передать её сюда в виде переменной, поэтому генерим заново из гет-параметров
   * @return FormModel|Object
   */
  protected function getSearchModel()
  {
    if (self::$searchModel) {
      return self::$searchModel;
    }

    self::$searchModel = Yii::createObject($this->searchModelClass);

    if (!Yii::$app->request instanceof \yii\web\Request) {
      return self::$searchModel;
    }

    $filters = $this->getRequestParams() ?: [];

    self::$searchModel->load(['FormModel' => $filters]);

    return self::$searchModel;
  }

  /**
   * Получить параметры фильтрации из запроса. Например Yii::$app->request->get('FormModel')
   * @return mixed
   */
  abstract protected function getRequestParams();

  /**
   * @return array [ключ_текущей_статы => ключ_статы_на_которую_переход]
   */
  abstract protected function getSupportedFilterFields();

  /**
   * @return array
   */
  protected function getFilterParams()
  {
    $urlParams = [];
    foreach ($this->getSupportedFilterFields() as $statKey => $complainKey) {
      $filterValue = $this->getSearchModel()->$statKey;
      if (!$filterValue) {
        continue;
      }
      $urlParams[$complainKey] = $filterValue;
    }
    return $urlParams;
  }

  /**
   * @return array
   */
  protected function getGroupParams()
  {
    $urlParams = [];
    if (!$this->getSearchModel()->groups) {
      $this->getSearchModel()->groups[] = Group::BY_DATES;
    }
    foreach (array_keys($this->row->groups) as $group) {
      $value = $this->row->groups[$group]->getValue();

      switch ($group) {
        case Group::BY_DATES:
          $urlParams['start_date'] = $urlParams['end_date'] = $value;
          break;
        case Group::BY_MONTH_NUMBERS:
          // пример $value="2018.06"
          $groupFormat = new MonthNumbers($value, $this->getSearchModel());
          $urlParams['start_date'] = $groupFormat->getLeftDate();
          $urlParams['end_date'] = $groupFormat->getRightDate();
          break;
        case Group::BY_WEEK_NUMBERS:
          // пример $value="2018.25"
          $groupFormat = new WeekNumbers($value, $this->getSearchModel());
          $period = $groupFormat->getWeekPeriod();
          $urlParams['start_date'] = $period[0]->format('Y-m-d');
          $urlParams['end_date'] = $period[1]->format('Y-m-d');
          break;
        case Group::BY_HOURS:
          // Принимаем во внимание, что в стате по жалобам нет возможности фильтровать по часам.
          // В связи с этим ожем в УРЛ подставить только целиком день.
          $urlParams['start_date'] = $urlParams['end_date'] = $this->getSearchModel()->dateTo;
          break;
        default:
          $urlParams['start_date'] = $this->getSearchModel()->dateFrom;
          $urlParams['end_date'] = $this->getSearchModel()->dateTo;
          if (!array_key_exists($group, $this->getSupportedGroupFields())) {
            break;
          }
          $urlParams[$this->getSupportedGroupFields()[$group]] = [$value];
      }
    }
    return $urlParams;
  }

  /**
   * @return array [ключ_текущей_статы => ключ_статы_на_которую_переход]
   */
  abstract protected function getSupportedGroupFields();
}
