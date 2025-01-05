<?php

namespace mcms\notifications\models;

use mcms\common\event\Event;
use mcms\common\helpers\ArrayHelper;
use mcms\modmanager\models\Module;
use mcms\notifications\components\push\Api;
use mcms\notifications\traits\BaseNotificationTrait;
use mcms\user\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "push_notifications".
 *
 * @property integer $id
 * @property string $header
 * @property string $message
 * @property integer $is_send
 * @property integer $is_important
 * @property integer $is_news
 * @property integer $from_module_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $language
 * @property integer $user_id
 * @property string $event
 * @property integer $model_id
 * @property integer $notifications_delivery_id
 * @property integer $from_user_id
 */
class PushNotification extends \yii\db\ActiveRecord
{
  use BaseNotificationTrait;

  const NOT_REGISTERED_ERROR = 'NotRegistered';
  const INVALID_REGISTRATION_ERROR = 'InvalidRegistration';

  const FIELD_IS_SEND = 'is_send';
  /** @var  Api */
  private $_api;

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
    return 'push_notifications';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['message', 'from_module_id', 'language', 'event'], 'required'],
      [['message', 'event'], 'string'],
      [['is_send', 'is_important', 'is_news', 'from_module_id', 'user_id', 'notifications_delivery_id', 'from_user_id', 'model_id'], 'integer'],
      [['header', 'language'], 'string', 'max' => 255],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'message' => Yii::_t('notifications.labels.notification_creation_message'),
      'header' => Yii::_t('notifications.labels.notification_creation_header'),
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
   * @param $header
   * @param $message
   * @param $isImportant
   * @param $isNews
   * @param $notificationsDeliveryId
   * @return bool
   */
  public static function createNotification(IdentityInterface $to, Module $fromModule, $header, $message, $isImportant, $isNews, $notificationsDeliveryId, $event, $model_id)
  {
    $model = new self();
    $model->header = $header;
    $model->message = $message;
    $model->from_module_id = $fromModule->id;
    $model->is_important = $isImportant;
    $model->is_news = $isNews;
    $model->event = $event;
    $model->model_id = $model_id;
    $model->language = $to->language ?: Notification::DEFAULT_LANG;
    $model->from_user_id = (Yii::$app instanceof Application ? null : Yii::$app->user->id);
    $model->user_id = $to->id;
    $model->notifications_delivery_id = $notificationsDeliveryId;

    return $model->save();
  }

  /**
   * @return string
   */
  public function getEventObjectUrl()
  {
    /** @var Event $eventClass */
    $eventClass = '\\' . $this->event;
    return $eventClass::getUrl($this->model_id);
  }

  /**
   * Отправка push уведомления
   * @return bool
   */
  public function send()
  {
    /** @var \mcms\user\Module $usersModule */
    $usersModule = Yii::$app->getModule('users');
    $pushTokens = UserPushToken::findAll(['user_id' => $this->user_id]);

    $url = $this->getEventObjectUrl();

    // Если не партнер, добавляем /admin/ к ссылке
    if (Yii::$app->authManager->checkAccess($this->user_id, $usersModule::PERMISSION_CAN_VIEW_ADMIN_CABINET)) {
      $url[0] = '/admin' . ArrayHelper::getValue($url, 0);
    }

    $link = $this->getEventObjectUrl() ? Url::to($url, true) : null;

    $sended = false;
    foreach ($pushTokens as $token) {
      $result = $this->getApi()->send($token->token, strip_tags($this->header), strip_tags($this->message), $link);

      if ($result) {
        // если на одно устройство пользователя сообщение успешно отправлено, считаем отправку успешной
        $sended = true;
      } else if ($this->getApi()->getResponse()) {
        $error = $this->getApi()->getResponse()->getContent();
        $errorArray = Json::decode($error);
        $errorText = ArrayHelper::getValue(
          ArrayHelper::getValue(
            ArrayHelper::getValue($errorArray, 'results', []),
            0, []),
          'error'
        );
        // tricky: т.к. мы не можем точно идентифицировать устройство пользователя, могут возникнуть расхождения между нашей бд и данными из Firebase Cloud Messaging. Поэтому возможны ошибки описанные ниже
        // если $errorText == 'NotRegistered' || $errorText == 'InvalidRegistration', значит у нас в базе сохранен не корректный токен. Удаляем его
        if ($errorText == self::NOT_REGISTERED_ERROR || $errorText == self::INVALID_REGISTRATION_ERROR) {
          $token->delete();
        }
      }
    }
    if ($sended) {
      $this->setSend();
      $this->save();
    }
    return $sended;
  }

  /**
   * @return Api
   */
  private function getApi()
  {
    if ($this->_api) return $this->_api;
    return $this->_api = new Api();
  }
}
