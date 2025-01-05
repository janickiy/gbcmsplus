<?php

namespace mcms\statistic\models\cs;

use mcms\statistic\models\Complain;
use mcms\statistic\models\mysql\Refund;

/**
 * Факт в columnstore
 */
class Fact
{
  const TYPE_HIT = 1;
  const TYPE_SUB = 2;
  const TYPE_REBILL = 3;
  const TYPE_OFF = 4;
  const TYPE_SOLD = 5;
  const TYPE_ONETIME = 6;

  const TYPE_COMPLAINT_TEXT = 7;
  const TYPE_COMPLAINT_CALL = 8;
  const TYPE_COMPLAINT_AUTO_24 = 9;
  const TYPE_COMPLAINT_AUTO_MOMENT = 10;
  const TYPE_COMPLAINT_AUTO_DUPLICATE = 11;
  const TYPE_COMPLAINT_CALL_MNO = 12;

  const TYPE_REFUND_RGK = 13;
  const TYPE_REFUND_MNO = 14;

  /**
   * @return array словарик ['тип жалобы' => 'тип факта']
   */
  public static function getComplaintTypes()
  {
    return [
      Complain::TYPE_TEXT => static::TYPE_COMPLAINT_TEXT,
      Complain::TYPE_CALL => static::TYPE_COMPLAINT_CALL,
      Complain::TYPE_AUTO_24 => static::TYPE_COMPLAINT_AUTO_24,
      Complain::TYPE_AUTO_MOMENT => static::TYPE_COMPLAINT_AUTO_MOMENT,
      Complain::TYPE_AUTO_DUPLICATE => static::TYPE_COMPLAINT_AUTO_DUPLICATE,
      Complain::TYPE_CALL_MNO => static::TYPE_COMPLAINT_CALL_MNO,
    ];
  }

  /**
   * @return array словарик ['тип возврата' => 'тип факта']
   */
  public static function getRefundTypes()
  {
    return [
      Refund::TYPE_RGK => static::TYPE_REFUND_RGK,
      Refund::TYPE_MNO => static::TYPE_REFUND_MNO,
    ];
  }
}
