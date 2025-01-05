<?php

namespace mcms\promo\components\queue\prelands;

use rgk\queue\BasePayload;

/**
 * Class Payload содержит данные прелендов по умолчанию
 * @package mcms\statistic\components\queue\postbacks
 */
class Payload extends BasePayload
{
  /**
   * @var integer ID пользователя
   */
  public $userId;
  /**
   * @var integer ID источника
   */
  public $sourceId;
  /**
   * @var integer ID потока
   */
  public $streamId;
  /**
   * @var integer тип преленда по умолчанию
   */
  public $type;

}