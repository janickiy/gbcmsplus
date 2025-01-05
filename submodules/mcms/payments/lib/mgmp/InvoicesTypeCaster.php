<?php

namespace mcms\payments\lib\mgmp;

use mcms\payments\models\UserBalanceInvoice;

/**
 * Приведение типов инвойсов из mgmp в mcms и обратно
 */
class InvoicesTypeCaster
{
  /** @const int Штраф */
  const TYPE_PENALTY = 2;
  /** @const int Компенсация */
  const TYPE_COMPENSATION = 3;
  /** @const int Начисление при конвертации */
  const TYPE_CONVERT_INCREASE = 7;
  /** @const int Списание при конвертации */
  const TYPE_CONVERT_DECREASE = 8;

  /** @var array Соответствие типов инвойсов MCMS_TYPE => MGMP_TYPE */
  private static $mcms2mgmp = [
    UserBalanceInvoice::TYPE_PENALTY => self::TYPE_PENALTY,
    UserBalanceInvoice::TYPE_COMPENSATION => self::TYPE_COMPENSATION,
    UserBalanceInvoice::TYPE_CONVERT_INCREASE => self::TYPE_CONVERT_INCREASE,
    UserBalanceInvoice::TYPE_CONVERT_DECREASE => self::TYPE_CONVERT_DECREASE,
  ];

  /** @var array Соответствие типов инвойсов MGMP_TYPE => MCMS_TYPE */
  private static $mgmp2mcms = [
    self::TYPE_PENALTY => UserBalanceInvoice::TYPE_PENALTY,
    self::TYPE_COMPENSATION => UserBalanceInvoice::TYPE_COMPENSATION,
    self::TYPE_CONVERT_INCREASE => UserBalanceInvoice::TYPE_CONVERT_INCREASE,
    self::TYPE_CONVERT_DECREASE => UserBalanceInvoice::TYPE_CONVERT_DECREASE,
  ];

  /**
   * Получить тип MCMS соответствующий указанному типу MGMP
   * @param int $mgmpType Тип инвойса MGMP
   * @param bool $defaultNull Если соответствие не определено, возвращать null вместо переданного типа
   * @return int|null
   */
  public static function mgmp2mcms($mgmpType, $defaultNull = false)
  {
    return isset(self::$mgmp2mcms[$mgmpType]) ? self::$mgmp2mcms[$mgmpType] : ($defaultNull ? null : $mgmpType);
  }

  /**
   * Получить тип инвойса MGMP соответствующий указанному типу MCMS
   * @param int $mcmsType Тип инвойса MCMS
   * @param bool $defaultNull Если соответствие не определено, возвращать null вместо переданного типа
   * @return int|null
   */
  public static function mcms2mgmp($mcmsType, $defaultNull = false)
  {
    return isset(self::$mcms2mgmp[$mcmsType]) ? self::$mcms2mgmp[$mcmsType] : ($defaultNull ? null : $mcmsType);
  }
}