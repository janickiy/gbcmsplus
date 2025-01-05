<?php

namespace mcms\statistic\models\resellerStatistic;

use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * Модель представляет собой ключ аггрегации для модель Item
 * У неё есть тип (день/неделя/месяц/реселлер) и само значение данного типа.
 *
 * Class Group
 * @package mcms\statistic\models\resellerStatistic
 */
class Group extends Object
{
  const WEEK = 'week';
  const DAY = 'day';
  const MONTH = 'month';

  /**
   * тип = (день/неделя/месяц/реселлер)
   * @var  string
   */
  public $groupType;
  /**
   * Значение типа группировки (ключ группировки)
   * @var  string
   */
  public $value;

  /** @var  ItemSearch */
  public $searchModel;

  /**
   * @param string $groupType
   * @param string $value
   * @return Group
   */
  public static function create($groupType, $value = null)
  {
    return new self(['groupType' => $groupType, 'value' => $value]);
  }

  /**
   * @return string
   */
  public function getLabel()
  {
    return self::getLabels($this->groupType);
  }

  /**
   * @return string
   */
  public function __toString()
  {
    return $this->getFormattedValue();
  }

  /**
   * Получить отформаттированное значение поля
   * @return string
   */
  public function getFormattedValue()
  {
    $value = $this->value;
    switch ($this->groupType) {
      case self::WEEK:
      case self::MONTH:
        $leftValue = $this->getDateLeftValue();
        $rightValue = $this->getDateRightValue();
        $value = sprintf('%s - %s', $leftValue, $rightValue);
        break;
    }
    return $value;
  }

  /**
   * Бывает отфильтровали по датам, а мы показываем в таблице самую левую строку как начало недели|месяца.
   * Вот чтобы такого не происходило, надо всегда проверять по дате, по которой отфильтровали
   * @return string
   */
  public function getDateLeftValue()
  {
    $defaultValue = $this->value;
    if (!$this->searchModel->dateFrom) return $defaultValue;

    if ($this->value >= $this->searchModel->dateFrom) return $defaultValue;

    return $this->searchModel->dateFrom;
  }

  /**
   * @see [[self::getDateLeftValue]]
   * @return string
   */
  public function getDateRightValue()
  {
    switch ($this->groupType) {
      case Group::MONTH:
        $rightValue = date('Y-m-t', strtotime($this->value));
        break;
      case Group::WEEK:
        $rightValue = date('Y-m-d', strtotime($this->value . ' +6days'));
        break;
      default:
        $rightValue = $this->value;
    }

    if (!$this->searchModel->dateTo) return $rightValue;

    if ($rightValue <= $this->searchModel->dateTo) return $rightValue;

    return $this->searchModel->dateTo;

  }

  /**
   * Подставляется в название столбца грида
   * @param string|null $label
   * @return string[]
   */
  public static function getLabels($label = null)
  {
    $labels = [
      self::DAY => Yii::_t('statistic.reseller_profit.day'),
      self::WEEK => Yii::_t('statistic.reseller_profit.week'),
      self::MONTH => Yii::_t('statistic.reseller_profit.month'),
    ];

    if (!$label) return $labels;

    return ArrayHelper::getValue($labels, $label);
  }
}