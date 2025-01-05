<?php

namespace mcms\statistic\components\queue\postbacks;

use rgk\queue\BasePayload;

/**
 * Class Payload содержит данные постбеков
 * @package mcms\statistic\components\queue\postbacks
 */
class Payload extends BasePayload
{
  const TYPE_SUBSCRIPTION = 1;
  const TYPE_REBILL = 2;
  const TYPE_ONETIME_SUBSCRIPTION = 3;
  const TYPE_SUBSCRIPTION_OFF = 4;
  const TYPE_SUBSCRIPTION_SELL = 5;
  const TYPE_COMPLAIN = 6;

  /**
   * @var array
   */
  public $hitIds;

  /**
   * @var int
   */
  public $type;

  /**
   * Запускать фейковый крон
   * @var bool
   */
  public $isDummyExec = false;
}
