<?php
namespace mcms\payments\lib\payprocess\components;

use mcms\common\helpers\ArrayHelper;
use mcms\payments\models\UserPayment;
use rgk\payprocess\components\handlers\AbstractPayHandler;
use yii\base\Object;

class StatusConverter extends Object
{
  const STATUSES_MAP = [
    AbstractPayHandler::STATUS_PROCESS => UserPayment::STATUS_PROCESS,
    AbstractPayHandler::STATUS_COMPLETED => UserPayment::STATUS_COMPLETED,
    AbstractPayHandler::STATUS_ERROR => UserPayment::STATUS_ERROR,
  ];

  /**
   * @param $payoutServiceStatus
   * @return int|bool
   */
  public static function payoutServiceToUserPayment($payoutServiceStatus)
  {
    return ArrayHelper::getValue(static::STATUSES_MAP, $payoutServiceStatus, false);
  }
}