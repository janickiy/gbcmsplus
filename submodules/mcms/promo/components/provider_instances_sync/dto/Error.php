<?php

namespace mcms\promo\components\provider_instances_sync\dto;

class Error implements \JsonSerializable
{
  /** @var string */
  public $name;

  /** @var string */
  public $message;

  /** @var int */
  public $code;

  /** @var int */
  public $status;

  /**
   * @return array
   */
  public function jsonSerialize()
  {
    return get_object_vars($this);
  }


}