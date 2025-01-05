<?php

namespace mcms\promo\queues;

use rgk\queue\BasePayload;

/**
 * Данные для воркера @see PartnerProgramSyncWorker
 */
class PartnerProgramSyncPayload extends BasePayload
{
  /** @var int ID пользователя запустившего синхронизацию */
  public $initiatorUserId;
  /** @var int ID пользователя для синхронизации партнерских программ */
  public $userId;
}