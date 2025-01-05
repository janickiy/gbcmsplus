<?php
namespace mcms\promo\queues;

use mcms\promo\components\PartnerProgramSync;
use mcms\user\models\User;
use rgk\queue\PayloadInterface;
use rgk\queue\WorkerInterface;
use Yii;

/**
 * Синхронизация профитов пользователя с партнерскими программами
 */
class PartnerProgramSyncWorker implements WorkerInterface
{
  const CHANNEL_NAME = 'partner_program_sync';

  /**
   * @param PayloadInterface|PartnerProgramSyncPayload $payload
   * @return bool
   * @throws \yii\db\Exception
   */
  public function work(PayloadInterface $payload)
  {
    if (!$payload->userId || !$payload->initiatorUserId) {
      Yii::warning(
        'Invalid PartnerProgramSyncWorker payload! ' .
        'userId: ' . $payload->userId . ' initiatorUserId: ' . $payload->initiatorUserId . PHP_EOL
      );

      return true;
    }

    $initiator = User::findOne($payload->initiatorUserId);
    if (!$initiator) {
      Yii::warning('Initiator user #' . $payload->initiatorUserId . ' not found' . PHP_EOL);
    }

    Yii::$app->user->setIdentity($initiator);

    return (new PartnerProgramSync(['userId' => $payload->userId]))->run();
  }

  /**
   * @inheritdoc
   */
  public function getChannelName()
  {
    return self::CHANNEL_NAME;
  }
}