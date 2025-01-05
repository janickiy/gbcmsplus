<?php

namespace mcms\notifications\components\invitations\queue;


use rgk\queue\BasePayload;

/**
 * Class BuilderPayload
 * @package mcms\notifications\components\invitations\queue
 */
class BuilderPayload extends BasePayload
{
  public $modelId;

  public $forceSend;
}