<?php

namespace mcms\holds\components;

use \mcms\holds\models\HoldProgramRule;
use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * Class UnholdSettingsDescription
 */
class UnholdSettingsDescription extends Object
{

  /**
   * Примерный ответ:
   * Расхолд полный 1 месяц | каждое 5 число месяца | не ранее чем через 7 дней
   *
   * @param HoldProgramRule $rule
   * @param string $glue
   * @return string
   */
  public static function getModelDescription(HoldProgramRule $rule, $glue = ' | ')
  {
    return implode($glue, array_filter([
      self::getUnholdRangeText($rule->unhold_range, $rule->unhold_range_type),
      self::getMinHoldRangeText($rule->min_hold_range, $rule->min_hold_range_type),
      self::getAtDayText($rule->at_day, $rule->at_day_type),
    ]));
  }

  /**
   * @param $value
   * @param $type
   * @return string
   */
  public static function getUnholdRangeText($value, $type)
  {
    return Yii::_t('holds.main.hold_stack_size') . ': ' . $value . ' ' . self::getRangeTypeName($type);
  }

  /**
   * @param null|int $type
   * @return null|string|array
   */
  public static function getRangeTypeName($type = null)
  {
    $items = [
      HoldProgramRule::UNHOLD_RANGE_TYPE_DAY => Yii::_t('holds.main.days'),
      HoldProgramRule::UNHOLD_RANGE_TYPE_WEEK => Yii::_t('holds.main.weeks'),
      HoldProgramRule::UNHOLD_RANGE_TYPE_MONTH => Yii::_t('holds.main.months'),
    ];

    if (!$type) return $items;

    return ArrayHelper::getValue($items, $type);
  }

  /**
   * @param $value
   * @param $type
   * @return null|string
   */
  public static function getAtDayText($value, $type)
  {
    if (!$value) return null;
    return Yii::_t('holds.main.at') . ': ' . $value . ' ' . self::getAtDayTypeName($type);
  }

  /**
   * @param $type
   * @return string|null|array
   */
  public static function getAtDayTypeName($type = null)
  {
    $items = [
      HoldProgramRule::AT_DAY_TYPE_WEEK => Yii::_t('holds.main.day_of_week'),
      HoldProgramRule::AT_DAY_TYPE_MONTH => Yii::_t('holds.main.day_of_month'),
    ];

    if (!$type) return $items;

    return ArrayHelper::getValue($items, $type);
  }

  /**
   * @param $value
   * @param $type
   * @return string
   */
  public static function getMinHoldRangeText($value, $type)
  {
    if (empty($value)) return null;
    return Yii::_t('holds.main.min_hold') . ': ' . $value . ' ' . self::getRangeTypeName($type);
  }
}