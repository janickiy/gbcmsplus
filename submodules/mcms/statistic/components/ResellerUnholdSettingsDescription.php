<?php

namespace mcms\statistic\components;

use mcms\statistic\models\ResellerHoldRule;
use Yii;
use yii\base\Object;

/**
 * Хэлпер для отображения описания правила расхолда для профитов реселлера.
 * Class ResellerUnholdSettingsDescription
 */
class ResellerUnholdSettingsDescription extends Object
{
  /**
   * Примерный ответ:
   * Расхолд полный 1 месяц | каждое 5 число месяца | не ранее чем через 7 дней
   *
   * @param ResellerHoldRule $rule
   * @param string $glue
   * @return string
   */
  public static function getModelDescription(ResellerHoldRule $rule, $glue = ' | ')
  {
    return implode($glue, array_filter([
      self::getUnholdRangeText($rule->unholdRange, $rule->unholdRangeType),
      self::getMinHoldRangeText($rule->minHoldRange, $rule->minHoldRangeType),
      self::getAtDayText($rule->atDay, $rule->atDayType),
    ]));
  }

  /**
   * @param $value
   * @param $type
   * @return string
   */
  public static function getUnholdRangeText($value, $type)
  {
    return Yii::_t('statistic.reseller_profit.hold_stack_size') . ": " .
      Yii::_t("statistic.reseller_profit.hold_stack_size_description_$type", ['value' => $value]);
  }

  /**
   * @param $value
   * @param $type
   * @return null|string
   */
  public static function getAtDayText($value, $type)
  {
    if (is_null($value)) return null;
    return Yii::_t("statistic.reseller_profit.at_day_$type", ['value' => $value]);
  }

  /**
   * @param $value
   * @param $type
   * @return string
   */
  public static function getMinHoldRangeText($value, $type)
  {
    if (empty($value)) return null;
    return Yii::_t('statistic.reseller_profit.min_hold') . ": " .
      Yii::_t("statistic.reseller_profit.min_hold_description_$type", ['value' => $value]);
  }
}