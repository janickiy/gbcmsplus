<?php

namespace mcms\notifications\models;

use mcms\common\helpers\ArrayHelper;
use mcms\notifications\components\telegram\Api;
use mcms\notifications\traits\BaseNotificationTrait;
use mcms\user\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use mcms\modmanager\models\Module;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\web\IdentityInterface;
use yii\console\Application;

/**
 * This is the model class for table "telegram_notifications".
 *
 * @property integer $id
 * @property string $message
 * @property integer $is_send
 * @property integer $is_important
 * @property integer $is_news
 * @property integer $from_module_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $language
 * @property integer $user_id
 * @property integer $notifications_delivery_id
 * @property integer $from_user_id
 */
class TelegramNotification extends ActiveRecord
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

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getModule()
  {
    return $this->hasOne(Module::class, ['id' => 'from_module_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUser()
  {
    return $this->hasOne(User::class, ['id' => 'user_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getDelivery()
  {
    return $this->hasOne(NotificationsDelivery::class, ['id' => 'notifications_delivery_id']);
  }

  /**
   * помечаем отправленным
   * @return $this
   */
  public function setSend()
  {
    $this->setAttribute(self::FIELD_IS_SEND, 1);
    return $this;
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'telegram_notifications';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['message', 'from_module_id', 'language'], 'required'],
      [['message'], 'string'],
      [['is_send', 'is_important', 'is_news', 'from_module_id', 'user_id', 'notifications_delivery_id', 'from_user_id'], 'integer'],
      [['language'], 'string', 'max' => 255],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'message' => Yii::_t('notifications.labels.notification_creation_message'),
      'user_id' => Yii::_t('notifications.labels.notification_creation_user'),
      'from_user_id' => Yii::_t('notifications.labels.notification_creation_from'),
      'created_at' => Yii::_t('notifications.labels.notification_creation_created'),
      'updated_at' => Yii::_t('notifications.labels.notification_creation_updated'),
      'is_important' => Yii::_t('notifications.labels.notification_creation_isImportant'),
      'is_send' => Yii::_t('notifications.labels.notification_creation_viewed'),
      'is_news' => Yii::_t('notifications.labels.notification_creation_isNews'),
      'from_module_id' => Yii::_t('notifications.labels.notification_creation_fromModule'),
    ];
  }

  /**
   * Создание уведомления
   * @param IdentityInterface $to
   * @param Module $fromModule
   * @param $message
   * @param $isImportant
   * @param $isNews
   * @param $notificationsDeliveryId
   * @return bool
   */
  public static function createNotification(IdentityInterface $to, Module $fromModule, $message, $isImportant, $isNews, $notificationsDeliveryId)
  {
    $model = new self();
    $model->message = $message;
    $model->from_module_id = $fromModule->id;
    $model->is_important = $isImportant;
    $model->is_news = $isNews;
    $model->language = $to->language ?: Notification::DEFAULT_LANG;
    $model->from_user_id = (Yii::$app instanceof Application ? null : Yii::$app->user->id);
    $model->user_id = $to->id;
    $model->notifications_delivery_id = $notificationsDeliveryId;

    return $model->save();
  }

  /**
   * Отправка telegram уведомления
   * @return bool
   */
  public function send()
  {
    $api = new Api();
    $userParams = Yii::$app->getModule('users')
      ->api('userParams', ['userId' => $this->getAttribute('user_id')])
      ->getResult();
    $telegramId = ArrayHelper::getValue($userParams, 'telegram_id');

    // Если потеряли $telegramId, то отправлять некому
    if (!$telegramId) {
      Yii::error('Не указан telegramID, невозможно отправить уведомление на телегу.
      Параметры пользователя: ' . Json::encode($this->attributes), __METHOD__);
      return false;
    }

    $result = $api->sendMessage($this->message, $telegramId);

    if ($result) {
      $this->setSend();
      $this->save();
    }
    return $result;
  }
}