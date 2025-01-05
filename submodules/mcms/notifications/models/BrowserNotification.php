<?php

namespace mcms\notifications\models;

use mcms\common\event\Event;
use mcms\common\multilang\MultiLangModel;
use mcms\modmanager\models\Module;
use mcms\notifications\traits\BaseNotificationTrait;
use mcms\user\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
use yii\console\Application;

/**
 * Class BrowserNotification
 * @package mcms\notifications\models
 * @property $is_viewed
 * @property $is_important
 * @property $is_news
 * @property $is_hidden
 * @property $event
 * @property $from
 * @property $header
 * @property $message
 * @property $from_module_id
 * @property $user_id
 * @property $model_id
 * @property $from_user_id
 * @property $event_instance
 */
class BrowserNotification extends MultiLangModel
{
  use BaseNotificationTrait;

  const FIELD_IS_VIEWED = 'is_viewed';

  const CACHE_KEY_FULL_COUNT = 'full_count';
  const CACHE_KEY_UNVIEWED_COUNT = 'unviewed_count';
  const CACHE_KEY_MAPPED_COUNT = 'mapped_count';

  const CACHE_TAG_NOTIFICATIONS = 'notifications';
  const CACHE_TAG_USER_NOTIFICATIONS = 'user_notifications';

  public $count;
  private static $_cacheTags;
  private static $_cacheKeys;

  public static function tableName()
  {
    return 'browser_notifications';
  }

  public function attributeLabels()
  {
    return [
      'user_id' => Yii::_t('notifications.labels.notification_creation_user'),
      'from_user_id' => Yii::_t('notifications.labels.notification_creation_from'),
      'header' => Yii::_t('notifications.labels.notification_creation_header'),
      'message' => Yii::_t('notifications.labels.notification_creation_message'),
      'created_at' => Yii::_t('notifications.labels.notification_creation_created'),
      'updated_at' => Yii::_t('notifications.labels.notification_creation_updated'),
      'is_important' => Yii::_t('notifications.labels.notification_creation_isImportant'),
      'from_module_id' => Yii::_t('notifications.labels.notification_creation_fromModule'),
      'is_viewed' => Yii::_t('notifications.labels.notification_creation_viewed'),
      'is_news' => Yii::_t('notifications.labels.notification_creation_isNews'),
    ];
  }

  /**
   * Получение тега или массива с тегами для кеширования
   * @param $tag string
   * @return array
   */
  public static function cacheTags($tag = null)
  {
    self::$_cacheTags = (self::$_cacheTags ? : [
      self::CACHE_TAG_NOTIFICATIONS => 'mcms.notifications.all',
      self::CACHE_TAG_USER_NOTIFICATIONS => 'mcms.notifcations.user{userId}',
    ]);

    return $tag ? self::$_cacheTags[$tag] : self::$_cacheTags;
  }

  /**
   * Получение ключа или массива с ключами для кеширования
   * @param $key string
   * @return type
   */
  public static function cacheKeys($key = null)
  {
    if (!self::$_cacheKeys) {
      self::$_cacheKeys = [
        self::CACHE_KEY_FULL_COUNT => 'mcms.notifications.full_count.user{userId}',
        self::CACHE_KEY_UNVIEWED_COUNT => 'mcms.notifications.unviewed_count.user{userId}',
        self::CACHE_KEY_MAPPED_COUNT => 'mcms.notifications.mapped_count.user{userId}',
      ];
    }

    return $key ? self::$_cacheKeys[$key] : self::$_cacheKeys;
  }

  /**
   * Очистка кеша
   * @param integer $userId
   */
  public static function invalidateCache($userId = null)
  {
    TagDependency::invalidate(Yii::$app->cache,
      $userId !== null
        ? strtr(static::cacheTags(self::CACHE_TAG_NOTIFICATIONS), ['{userId}' => $userId])
        : static::cacheTags(self::CACHE_TAG_NOTIFICATIONS)
    );
  }

  public static function setViewedByModelId($modelId, $event, $userId = null)
  {
    $condition = [
      'event' => $event,
      'model_id' => $modelId,
    ];

    if ($userId) $condition['user_id'] = $userId;

    static::setViewedByCondition($condition);

    TagDependency::invalidate(
      Yii::$app->cache,
      BrowserNotification::cacheTags(BrowserNotification::CACHE_TAG_NOTIFICATIONS)
    );
  }

  public static function setViewedById($id, $event, $userId = null)
  {
    $condition = [
      'event' => $event,
      'id' => $id,
    ];

    if ($userId) $condition['user_id'] = $userId;

    static::setViewedByCondition($condition);

    TagDependency::invalidate(
      Yii::$app->cache,
      BrowserNotification::cacheTags(BrowserNotification::CACHE_TAG_NOTIFICATIONS)
    );
  }

  private static function setViewedByCondition(array $condition)
  {
    /** @var \mcms\notifications\models\BrowserNotification $browserNotification */
    foreach (self::find()->where($condition)->each() as $browserNotification) {
      $browserNotification->setRead()->save();
    }
  }

  /**
   * Отмечает уведомления как прочитанные
   * @param int $userId
   * @param int $modelId
   * @param string $event
   * @param boolean $setHidden Нужно ли скрывать уведомления
   * @return type
   */
  public static function setViewed($userId, $modelId, $event, $setHidden = false)
  {
    $conditions = [];
    if ($userId) $conditions['user_id'] = $userId;
    if ($modelId) $conditions['model_id'] = $modelId;
    if ($event) $conditions['event'] = $event;
    if ($setHidden) $conditions['is_hidden'] = 0;

    $update = ['is_viewed' => 1];
    if ($setHidden) $update['is_hidden'] = 1;

    $result = BrowserNotification::updateAll($update, $conditions);

    TagDependency::invalidate(Yii::$app->cache, [
      $userId
        ? strtr(BrowserNotification::cacheTags(BrowserNotification::CACHE_TAG_USER_NOTIFICATIONS), ['{userId}' => $userId])
        : BrowserNotification::cacheTags(BrowserNotification::CACHE_TAG_NOTIFICATIONS),
    ]);

    return $result;
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

  public function getMultilangAttributes()
  {
    return ['from', 'header', 'message'];
  }

  public function getUser()
  {
    return $this->hasOne(User::class, ['id' => 'user_id']);
  }

  public function getModule()
  {
    return $this->hasOne(Module::class, ['id' => 'from_module_id']);
  }

  public static function findUnReaderByUser(User $user)
  {
    return static::find()->where([
      'user_id' => $user->id,
      self::FIELD_IS_VIEWED => 0,
    ]);
  }

  public function setRead()
  {
    $this->setAttribute(self::FIELD_IS_VIEWED, 1);
    return $this;
  }

  public static function createNotification(User $user, Module $fromModule, $from, $header, $message, $event, $model_id, $isImportant, $isNews, Event $eventInstance, $notificationsDeliveryId)
  {
    $model = new self();
    $model->from = $from;
    $model->header = $header;
    $model->message = $message;
    $model->is_hidden = 0;
    $model->from_module_id = $fromModule->id;
    $model->user_id = $user->id;
    $model->is_important = $isImportant;
    $model->is_news = $isNews;
    $model->event = $event;
    $model->model_id = $model_id;
    $model->from_user_id = (Yii::$app instanceof Application ? null : Yii::$app->user->id);
    $model->event_instance = serialize($eventInstance);
    $model->notifications_delivery_id = $notificationsDeliveryId;
    $saveResult = $model->save();

    return $saveResult;
  }

  /**
   * @inheritdoc
   */
  public function afterSave($insert, $changedAttributes)
  {
    parent::afterSave($insert, $changedAttributes);
    static::invalidateCache($this->user_id);
  }

  /**
   * @inheritdoc
   */
  public function afterDelete()
  {
    parent::afterDelete();
    static::invalidateCache($this->user_id);
  }

  /**
   * @return Event
   */
  public function getEventObjectInstance()
  {
    return @unserialize($this->event_instance);
  }

  /**
   * @return string
   */
  public function getEventObjectUrl()
  {
    $eventClass = '\\' . $this->event;
    return $eventClass::getUrl($this->model_id);
  }

  public function getDelivery()
  {
    return $this->hasOne(NotificationsDelivery::class, ['id' => 'notifications_delivery_id']);
  }

  /**
   * Проверка доступа к просмотру записи
   * @return bool
   */
  public function hasAccess()
  {
    return \Yii::$app->user->can('NotificationsNotificationsBrowserNotOwn') || $this->from_user_id == Yii::$app->user->id;
  }
}