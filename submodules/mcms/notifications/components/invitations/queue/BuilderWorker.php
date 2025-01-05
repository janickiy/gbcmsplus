<?php

namespace mcms\notifications\components\invitations\queue;


use mcms\notifications\components\invitations\EmailInvitationsBuilder;
use mcms\notifications\models\UserInvitationEmail;
use rgk\queue\PayloadInterface;
use rgk\queue\WorkerInterface;

/**
 * Class Worker
 * @package mcms\notifications\components\invitations\queue
 */
class BuilderWorker implements WorkerInterface
{
  /**
   * Имя канала очереди
   */
  const CHANNEL_NAME = 'invitations_emails_builder';

  /**
   * @inheritdoc
   */
  public function getChannelName()
  {
    return self::CHANNEL_NAME;
  }

  /**
   * @param BuilderPayload|PayloadInterface $payload
   * @return bool
   */
  public function work(PayloadInterface $payload)
  {
    $model = UserInvitationEmail::findOne($payload->modelId);
    if (!$model) {
      return false;
    }

    $sender = new EmailInvitationsBuilder($model, [
      'useQueue' => false,
    ]);

    return $sender->run($payload->forceSend);
  }
}