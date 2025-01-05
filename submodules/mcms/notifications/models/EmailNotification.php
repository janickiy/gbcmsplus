<?php

namespace mcms\notifications\models;

use mcms\notifications\components\event\EmailSendEvent;
use mcms\notifications\traits\BaseNotificationTrait;
use mcms\user\models\User;
use Yii;
use mcms\modmanager\models\Module;
use yii\behaviors\TimestampBehavior;
use yii\console\Application;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use yii\swiftmailer\Message;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "email_notifications".
 *
 * @property string $id
 * @property string $from
 * @property string $header
 * @property string $message
 * @property integer $is_send
 * @property integer $is_important
 * @property integer $is_news
 * @property integer $from_module_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $username
 * @property string $email
 * @property integer $language
 * @property integer $from_user_id
 * @property integer $to_user_id
 * @property integer $notifications_delivery_id
 *
 */
class EmailNotification extends ActiveRecord
{
  use BaseNotificationTrait;

  const FIELD_IS_SEND = 'is_send';

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
    ];
  }

  public function attributeLabels()
  {
    return [
      'to_user_id' => Yii::_t('notifications.labels.notification_creation_user'),
      'from_user_id' => Yii::_t('notifications.labels.notification_creation_from'),
      'username' => Yii::_t('notifications.labels.notification_creation_user'),
      'header' => Yii::_t('notifications.labels.notification_creation_header'),
      'created_at' => Yii::_t('notifications.labels.notification_creation_created'),
      'updated_at' => Yii::_t('notifications.labels.notification_creation_updated'),
      'is_important' => Yii::_t('notifications.labels.notification_creation_isImportant'),
      'is_send' => Yii::_t('notifications.labels.notification_creation_viewed'),
      'is_news' => Yii::_t('notifications.labels.notification_creation_isNews'),
    ];
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'email_notifications';
  }


  public function getModule()
  {
    return $this->hasOne(Module::class, ['id' => 'from_module_id']);
  }

  public function getUser()
  {
    return $this->hasOne(User::class, ['id' => 'to_user_id']);
  }

  public function getDelivery()
  {
    return $this->hasOne(NotificationsDelivery::class, ['id' => 'notifications_delivery_id']);
  }

  public function setSend()
  {
    $this->setAttribute(self::FIELD_IS_SEND, 1);
    return $this;
  }

  public static function createNotification(IdentityInterface $to, Module $fromModule, $from, $header, $message, $isImportant, $isNews, $notificationsDeliveryId)
  {
    $model = new self();
    $model->from = $from;
    $model->header = $header;
    $model->message = $message;
    $model->from_module_id = $fromModule->id;
    $model->username = $to->username;
    $model->email = $to->email;
    $model->is_important = $isImportant;
    $model->is_news = $isNews;
    $model->language = $to->language ?: Notification::DEFAULT_LANG;
    $model->from_user_id = (Yii::$app instanceof Application ? null : Yii::$app->user->id);
    $model->to_user_id = $to->id;
    $model->notifications_delivery_id = $notificationsDeliveryId;
    return $model->save();
  }

  /**
   * Проверка доступа к просмотру записи
   * @return bool
   */
  public function hasAccess()
  {
    return \Yii::$app->user->can('NotificationsNotificationsEmailNotOwn') || $this->from_user_id == Yii::$app->user->id;
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

    Yii::$app->language = $this->language;
    $from = $this->getAttribute('from');
    $subject = $this->getAttribute('header');
    $body = $this->getAttribute('message');
    $to = $this->username
      ? [$this->email => $this->username]
      : $this->email;

    $event = new EmailSendEvent();
    $event->from = json_encode([$from => $fromEmailCopyright]);
    $event->to = json_encode($to);
    $event->header = $subject;
    $event->template = $body;

    $unsubscribeUrl = null;
    if ($user = $this->user) {
      $token = $user->email_unsubscribe_token;

      if (!$token) {
        $user->generateEmailUnsuscribeToken();
        $user->save();

        $token = $user->email_unsubscribe_token;
      }

      $unsubscribeUrl = Url::to(['/users/api/email-unsubscribe', 'token' => $token]);
    }

    /** @var Message $email */
    $email = Yii::$app->mailer->compose()
      ->setFrom([$from => $fromEmailCopyright])
      ->setSubject($subject)
      ->setHtmlBody($partnersModule->api('getEmailTemplate', [
        'subject' => $subject,
        'body' => $body,
        'email' => $this->email,
        'unsubscribeUrl' => $unsubscribeUrl,
      ])->getResult())
      ->setTo($to);

    $unsubscribeUrl && $email->addHeader('List-Unsubscribe', "<$unsubscribeUrl>");

    $event->language = $this->language;

    $result = true;
    if ($email->send()) {
      $event->status = 1;
      $this->setSend();
      $this->save();
    } else {
      $event->status = 0;
      $result = false;
    }

    $event->trigger();
    return $result;
  }
}
