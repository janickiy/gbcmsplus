<?php

namespace mcms\notifications\models;


use mcms\common\event\Event;
use mcms\common\helpers\Link;
use mcms\user\models\User;
use Yii;
use mcms\modmanager\models\Module;
use yii\behaviors\TimestampBehavior;
use mcms\common\multilang\MultiLangModel;
use yii\helpers\Json;

/**
 * Class NotificationsDelivery
 * @package mcms\notifications\models
 *
 * @property int $id
 * @property int $user_id
 * @property int $is_important
 * @property int $from_module_id
 * @property int $created_at
 * @property int $updated_at
 * @property int $is_manual
 * @property int $notification_type
 * @property string $header
 * @property string $message
 * @property string $event
 * @property string $roles
 * @property string $emails
 * @property string $is_news
 */
class NotificationsDelivery extends MultiLangModel
{
  private $_eventObject;

  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'user_id' => Yii::_t('notifications.labels.notification_creation_user'),
      'header' => Yii::_t('notifications.labels.notification_creation_header'),
      'is_important' => Yii::_t('notifications.labels.notification_creation_isImportant'),
      'from_module_id' => Yii::_t('notifications.main.module_name'),
      'created_at' => Yii::_t('notifications.labels.notification_creation_created'),
      'updated_at' => Yii::_t('notifications.labels.notification_creation_updated'),
      'is_manual' => Yii::_t('notifications.labels.notification_creation_manual'),
      'is_news' => Yii::_t('notifications.labels.notification_creation_isNews'),
      'event' => Yii::_t('notifications.main.event'),
      'roles' => Yii::_t('notifications.labels.notification_creation_roles'),
      'notification_type' => Yii::_t('notifications.labels.notification_creation_notificationType'),
      'emails' => 'Email',
    ];
  }

  public function getMultilangAttributes()
  {
    return ['header', 'message'];
  }

  /**
   * @inheritDoc
   */
  public static function tableName()
  {
    return '{{%notifications_delivery}}';
  }

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
    ];
  }

  public function rules()
  {
    return array_merge(
      parent::rules(), [
        [['header', 'message'], 'filter', 'filter' => 'mcms\common\multilang\MultiLangModel::filterArrayPurifier'],
        [['header', 'roles'], 'safe'],
        [['emails', 'event'], 'string'],
        [['message', 'notification_type'], 'required'],
        [['user_id', 'from_module_id'], 'integer'],
        [['is_important', 'is_manual', 'is_news'], 'boolean'],
      ]
    );
  }

  public function getModule()
  {
    return $this->hasOne(Module::class, ['id' => 'from_module_id']);
  }

  public function getUser()
  {
    return $this->hasOne(User::class, ['id' => 'user_id']);
  }

  /**
   * @return string
   */
  public function getUserLink()
  {
    return !$this->user
      ? null
      : Link::get(
        '/users/users/view',
        ['id' => $this->user->id],
        ['data-pjax' => 0],
        $this->user->getStringInfo()
      );
  }

  /**
   * @return mixed
   */
  public function getNotificationType()
  {
    return $this->getAttribute('notification_type');
  }

  /**
   * @return array
   */
  public function getRolesAsArray()
  {
    return (array)Json::decode($this->roles);
  }

  /**
   * @param array $roles
   */
  public function setRolesArray(array $roles = [])
  {
    $this->roles = Json::encode($roles);
  }

  /**
   * Проверка доступа к просмотру рассылки
   * @return bool
   */
  public function hasAccess()
  {
    return \Yii::$app->user->can('NotificationsDeliveryNotOwn') || $this->user_id == Yii::$app->user->id;
  }


  /**
   * Варианты событий для фильтра
   * @return array
   */
  public static function getEventFilterVariants()
  {
    $key = 'notifications-delivery-event-variants';
    $cache = \Yii::$app->cache;
    $variants = $cache->get($key);
    if ($variants) return $variants;

    $variants = [];
    $events = static::find()
      ->andWhere(['not', ['event' => null]])
      ->select('event')
      ->distinct('event')
      ->asArray()
      ->column();

    /** @var string|Event $eventClass */
    foreach ($events as $eventClass) {
      if (class_exists($eventClass)) {
        /** @var Event $eventObject */
        $eventObject = new $eventClass;
        $variants[$eventClass] = $eventObject->getEventName();
      } else {
        $classParts = explode('\\', $eventClass);
        $variants[$eventClass] = end($classParts);
      }
    }

    $cache->set($key, $variants, 3600);

    return $variants;
  }

  /**
   * Получить экземпляр класса события
   * @return null|Event
   */
  public function getEventObject()
  {
    if ($this->_eventObject) return $this->_eventObject;

    return class_exists($this->event)
      ? $this->_eventObject = new $this->event
      : null;
  }

  /**
   * @return array
   */
  public function getNotificationsUrl()
  {
    switch ($this->notification_type) {
      case Notification::NOTIFICATION_TYPE_BROWSER:
        return ['/notifications/notifications/browser/', 'BrowserNotificationSearch[notifications_delivery_id]' => $this->id];
        break;
      case Notification::NOTIFICATION_TYPE_TELEGRAM:
        return ['/notifications/notifications/telegram/', 'TelegramNotificationSearch[notifications_delivery_id]' => $this->id];
        break;
      case Notification::NOTIFICATION_TYPE_EMAIL:
        return ['/notifications/notifications/email/', 'EmailNotificationSearch[notifications_delivery_id]' => $this->id];
        break;
      case Notification::NOTIFICATION_TYPE_PUSH:
        return ['/notifications/notifications/push/', 'PushNotificationSearch[notifications_delivery_id]' => $this->id];
        break;
    }
    return [];
  }
}