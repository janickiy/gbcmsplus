<?php

namespace mcms\notifications\components\invitations;

use mcms\notifications\components\invitations\queue\BuilderPayload;
use mcms\notifications\components\invitations\queue\BuilderWorker;
use mcms\notifications\models\UserInvitationEmail;
use mcms\notifications\models\UserInvitationEmailSent;
use mcms\user\models\UserInvitation;
use Yii;
use yii\base\BaseObject;


/**
 * Class EmailInvitationsSender
 * @package mcms\notifications\components\invitations
 */
class EmailInvitationsBuilder extends BaseObject
{
  /**
   * @var bool
   */
  public $useQueue = true;

  /**
   * @var array id приглашений, на которые отправить уведомления (если не указали - отправить всем)
   */
  public $invitationsIds = [];

  /**
   * @var UserInvitationEmail
   */
  protected $model;

  /**
   * EmailInvitationsSender constructor.
   * @param UserInvitationEmail $model
   * @param array $config
   */
  public function __construct(UserInvitationEmail $model, array $config = [])
  {
    $this->model = $model;

    parent::__construct($config);
  }

  /**
   * @inheritdoc
   */
  public function init()
  {
    if (Yii::$app->has('queue') === false) {
      $this->useQueue = false;
    }
  }

  /**
   * @param bool $forceSend
   * @return bool
   */
  public function run($forceSend = false)
  {
    if (!$this->useQueue) {
      return $this->createEmails($forceSend);
    }

    $payload = new BuilderPayload([
      'modelId' => $this->model->id,
      'forceSend' => $forceSend,
    ]);

    return Yii::$app->queue->push(BuilderWorker::CHANNEL_NAME, $payload);
  }

  /**
   * @param bool $forceSend
   * @return bool
   */
  protected function createEmails($forceSend)
  {
    $invitations = UserInvitation::find()
      ->andWhere(['status' => UserInvitation::STATUS_AWAITING]);

    if ($this->invitationsIds) {
      $invitations->andWhere(['id' => $this->invitationsIds]);
    }

    // если не требуется отправлять всем, оставляем тех, кому это письмо не отправлялось
    if (!$forceSend) {
      $invitations->joinWith('emailSent es', false);
      $invitations->andWhere(['es.id' => null]);
    }

    foreach ($invitations->each() as $invitation) {
      /** @var UserInvitation $invitation */

      $emailSend = new UserInvitationEmailSent([
        'invitation_email_id' => $this->model->id,
        'invitation_id' => $invitation->id,
        'from' => $this->model->from,
        'to' => $invitation->username,
        'header' => $this->model->replaceHeader($invitation),
        'message' => $this->model->replaceMessage($invitation),
      ]);

      $emailSend->save();
    }

    $this->model->is_complete = 1;
    $this->model->save();

    return true;
  }
}