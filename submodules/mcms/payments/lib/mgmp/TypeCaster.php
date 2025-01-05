<?php

namespace mcms\payments\lib\mgmp;

use mcms\payments\models\UserPayment;

/**
 * Приведение статусов выплат из mgmp в mcms и обратно
 */
class TypeCaster
{
  /** @const string Выплата отменена */
  const STATUS_CANCELED = 'canceled';
  /** @const string Выплата в процессе */
  const STATUS_PROCESS = 'process';
  /** @const string Выплата в ожидании */
  const STATUS_AWAITING = 'awaiting';
  /** @const string Выплата выполнена */
  const STATUS_COMPLETED = 'done';

  /**
   * Соответствие статусов MCMS => MGMP
   * @var string[]
   */
  private static $mcms2mgmp = [
    UserPayment::STATUS_PROCESS => self::STATUS_PROCESS,
    UserPayment::STATUS_AWAITING => self::STATUS_AWAITING,
    UserPayment::STATUS_DELAYED => self::STATUS_AWAITING,
  ];

  /**
   * Соответствие статусов MGMP => MCMS
   * @var string[]
   */
  private static $mgmp2mcms = [
    self::STATUS_CANCELED => UserPayment::STATUS_ERROR,
    self::STATUS_PROCESS => UserPayment::STATUS_PROCESS,
    self::STATUS_AWAITING => UserPayment::STATUS_PROCESS, // то что в мгмп в ожидании у нас в процессе
    self::STATUS_COMPLETED => UserPayment::STATUS_COMPLETED,
  ];

  /**
   * Получить статус MCMS соответствующий указанному статусу MGMP
   * @param string $mgmpStatus
   * @param bool $defaultNull Если соответствие не определено, возвращать null вместо переданного статуса
   * @return string|null
   */
  public static function mgmp2mcms($mgmpStatus, $defaultNull = false)
  {
    return isset(self::$mgmp2mcms[$mgmpStatus]) ? self::$mgmp2mcms[$mgmpStatus] : ($defaultNull ? null : $mgmpStatus);
  }

  /**
   * Получить статус MCMS соответствующий указанному статусу MGMP
   * @param string $mcmsStatus
   * @param bool $defaultNull Если соответствие не определено, возвращать null вместо переданного статуса
   * @return string|null
   */
  public static function mcms2mgmp($mcmsStatus, $defaultNull = false)
  {
    return isset(self::$mcms2mgmp[$mcmsStatus]) ? self::$mcms2mgmp[$mcmsStatus] : ($defaultNull ? null : $mcmsStatus);
  }
}