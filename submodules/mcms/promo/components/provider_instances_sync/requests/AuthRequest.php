<?php

namespace mcms\promo\components\provider_instances_sync\requests;

use mcms\promo\components\provider_instances_sync\RequestInterface;

class AuthRequest implements RequestInterface
{
  /** @var string */
  public $email;

  /** @var string */
  public $hash;

  /**
   * @return array
   */
  public function getRequestData()
  {
    return [
      'email' => $this->email,
      'hash' => $this->hash,
    ];
  }
}