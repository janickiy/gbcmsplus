<?php

namespace mcms\notifications\models;


use mcms\user\models\UserInvitation;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class EmailInvitation
 * @package mcms\notifications\models
 *
 * @property int $id [int(10) unsigned]
 * @property int $invitation_email_id [int(10) unsigned]
 * @property int $invitation_id [int(10) unsigned]
 * @property string $from [varchar(255)]
 * @property string $to [varchar(255)]
 * @property string $header [varchar(255)]
 * @property string $message
 * @property bool $is_sent [tinyint(1) unsigned]
 * @property bool $attempts [tinyint(1) unsigned]
 * @property string $error [varchar(255)]
 * @property int $created_at [int(10) unsigned]
 * @property int $updated_at [int(10) unsigned]
 *
 * @property UserInvitationEmail $invitationEmail
 * @property UserInvitation $invitation
 */
class UserInvitationEmailSent extends ActiveRecord
{
  /**
   * @return string
   */
  public static function tableName()
  {
    return 'users_invitations_emails_sent';
  }

  /**
   * @return array
   */
  public function rules()
  {
    return [
      [['invitation_id', 'is_sent', 'attempts'], 'integer'],
      [['from', 'to', 'header', 'message', 'error'], 'string'],
      [['from', 'to', 'header'], 'required'],
    ];
  }

  /**
   * @return array
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
    ];
  }

  /**
   * @return array
   */
  public function attributeLabels()
  {
    return [
      'id' => Yii::_t('notifications.invitations.attribute-id'),
      'invitation_email_id' => Yii::_t('notifications.invitations.attribute-invitation_email_id'),
      'invitation_id' => Yii::_t('notifications.invitations.attribute-invitation_id'),
      'from' => Yii::_t('notifications.invitations.attribute-from'),
      'to' => Yii::_t('notifications.invitations.attribute-to'),
      'header' => Yii::_t('notifications.invitations.attribute-header'),
      'message' => Yii::_t('notifications.invitations.attribute-message'),
      'is_sent' => Yii::_t('notifications.invitations.attribute-is_sent'),
      'attempts' => Yii::_t('notifications.invitations.attribute-attempts'),
      'error' => Yii::_t('notifications.invitations.attribute-error'),
      'created_at' => Yii::_t('notifications.invitations.attribute-created_at'),
      'updated_at' => Yii::_t('notifications.invitations.attribute-updated_at'),
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getInvitation()
  {
    return $this->hasOne(UserInvitation::class, ['id' => 'invitation_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getInvitationEmail()
  {
    return $this->hasOne(UserInvitationEmail::class, ['id' => 'invitation_email_id']);
  }

  /**
   *
   */
  public function setSent()
  {
    $this->is_sent = 1;
  }

  /**
   *
   */
  public function incrementAttempt()
  {
    $this->attempts++;
  }

  /**
   * Отправка email
   * @return bool
   */
  public function send()
  {
    /** @var \mcms\partners\Module $partnersModule */
    $partnersModule = Yii::$app->getModule('partners');
    $fromEmailCopyright = $partnersModule->getProjectName();

    try {
      $email = Yii::$app->mailer->compose()
        ->setFrom([$this->from => $fromEmailCopyright])
        ->setSubject($this->header)
        ->setHtmlBody($partnersModule->api('getEmailTemplate', [
          'subject' => $this->header,
          'body' => $this->message,
          'email' => $this->to,
        ])->getResult())
        ->setTo($this->to);

      if ($email->send()) {
        $this->setSent();
        $this->save();

        return true;
      }

    } catch (\Throwable $e) {
      Yii::error($e->getMessage(), __METHOD__);
    }

    $this->incrementAttempt();
    $this->save();

    return false;
  }
}