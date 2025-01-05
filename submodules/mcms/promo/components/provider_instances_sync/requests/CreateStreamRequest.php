<?php

namespace mcms\promo\components\provider_instances_sync\requests;

use mcms\promo\components\provider_instances_sync\RequestInterface;

/**
 * Class CreateStreamRequest
 * @package mcms\promo\components\provider_instances_sync\requests
 */
class CreateStreamRequest implements RequestInterface
{
  /** @var string */
  public $name;

  /** @var string */
  public $postbackUrl;

  /** @var string */
  public $trafficbackUrl;

  /** @var string */
  public $complainUrl;

  /** @var string */
  public $secretKey;

  /** @var bool */
  public $postbackGrouping = true;

  /**
   * @inheritdoc
   */
  public function getRequestData()
  {
    return [
      'name' => $this->name,
      'postback_url' => $this->postbackUrl,
      'trafficback_url' => $this->trafficbackUrl,
      'complain_url' => $this->complainUrl,
      'secret_key' => $this->secretKey,
      'postback_grouping' => $this->postbackGrouping,
    ];
  }
}