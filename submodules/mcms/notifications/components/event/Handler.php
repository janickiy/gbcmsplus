<?php

namespace mcms\notifications\components\event;

use mcms\common\event\Event;
use mcms\notifications\components\event\driver\Browser;
use mcms\notifications\components\event\driver\DriverInterface;
use mcms\notifications\components\event\driver\Email;
use mcms\notifications\components\event\driver\IncorrectDriverTypeException;
use mcms\notifications\components\event\driver\Push;
use mcms\notifications\components\event\driver\Telegram;
use mcms\notifications\models\Notification;
use mcms\notifications\models\NotificationForRoles;
use mcms\notifications\models\NotificationsDelivery;
use mcms\user\models\User;
use Yii;
use yii\console\Application;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class Handler
{
  protected $useOwner;
  protected $event;

  /** @var  Notification */
  protected $notificationModel;

  /** @var  DriverInterface */
  private $driver;

  /**
   * Handler constructor.
   * @param Notification $notification
   * @param Event $event
   * @throws IncorrectDriverTypeException
   */
  public function __construct(Notification $notification, Event $event)
  {
    $this->notificationModel = $notification;
    $this->event = $event;

    if ($this->notificationModel->notification_type == Notification::NOTIFICATION_TYPE_EMAIL) {
      $this->notificationModel->emails = implode(
        ',',
        array_merge(
          $this->notificationModel->emails ? explode(',', $this->notificationModel->emails) : [],
          $this->event->addEmails ? explode(',', $this->event->addEmails) : []
        )
      );
    }

    $this->initDriver();
  }


  public function sendNotification()
  {
    if (!$this->event->shouldSendNotification()) return ;
    if (!$this->notificationModel->isOwner()) {
      // Добавление в очередь на отправку уведомлений по ролям
      $roles = $this->notificationModel->getRolesAsArray();
      if ($roles) $this->saveForSendToSelectedRoles($roles);
    } else {
      // Отправка уведомления owner'у
      $this->saveNotificationsDelivery();
      $this->driver->send($this->event->owner);
    }

    // Отправка уведомлений на указанные email'ы
    $emails = explode(',', $this->notificationModel->emails);
    if (strlen($this->notificationModel->emails) && count($emails)) {
      $this->saveNotificationsDelivery();
      $this->sendAdditionalEmails($emails);
    }
  }

  /**
   * Отправка уведомлений на указанные email'ы
   * @param array $emails
   */
  private function sendAdditionalEmails(array $emails)
  {
    $handler = new self($this->notificationModel, $this->event);
    $handler->driver = Yii::createObject([
      'class' => Email::class,
      'notificationModel' => $this->notificationModel,
      'notificationsDelivery' => $this->driver->notificationsDelivery,
      'event' => $this->event
    ]);
    array_map(function($email) use ($handler) {
      $user = new User();
      $user->email = $email;
      $user->status = User::STATUS_ACTIVE;
      $user->language = $this->notificationModel->emails_language;
      $handler->driver->send($user);
    }, $emails);
  }

  /**
   * Добавление уведомлений в очередь для отправки по крону
   * @param string[] $roles Роли
   * @return bool
   */
  private function saveForSendToSelectedRoles($roles)
  {
    $notification = new NotificationForRoles([
      'roles' => $roles,
      'is_replace' => $this->notificationModel->isReplace,
      'user_id' => Yii::$app->user->id,
      'event_instance' => serialize($this->event)
    ]);
    $notification->setAttributes($this->notificationModel->attributes);

    $result = $notification->save();
    if (!$result) {
      Yii::error('Не удалось создать уведомления по ролям. 
      Уведомление: ' . Json::encode($notification->attributes) . '. ' .
      'Ошибки: ' . Json::encode($notification->getErrors()), __METHOD__);
    }

    return $result;
  }

  /**
   * Отправка нотификация по ролям
   * @param integer $userId ID пользователя для создания рассылки от этого пользователя
   * TRICKY сохраняем рассылку перед отправкой нотификация чтобы получить notifications_delivery_id
   */
  public function sendToSelectedRoles($userId)
  {
    $this->saveNotificationsDelivery($userId);

    /** @var \yii\data\ActiveDataProvider $query */
    $searchFilter = [];
    if ($ownerUserId = $this->event->owner instanceof User ? $this->event->owner->id : null) {
      if (!Yii::$app instanceof Application) {
        $searchFilter = [['<>', 'id', $ownerUserId]];
      }
    }

    $roles = $this->notificationModel->isNewRecord
      ? $this->notificationModel->getRoles()
      : ArrayHelper::map($this->notificationModel->getRoles()->asArray()->all(), 'name', 'name')
      ;

    // TRICKY API User вернет всех пользователей, если !$roles, поэтому при отсутствии ролей не выполняем отправку никому
    if (!$roles) return;

    $query = Yii::$app->getModule('users')
      ->api('user', [
        'ignoreNotAvailableUsers' => Yii::$app->user->can('NotificationsSkipIgnoreIdsCheck'),
        'onlyActiveUsers' => !$this->notificationModel->is_important,
      ])
      ->setResultTypeDataProvider()
      ->search(
        $searchFilter,
        true,
        null,
        true,
        $roles
      )->query;

    foreach ($query->each() as $user) {
      $this->event->setOwner($user);
      $this->driver->send($user);
    }
  }

  /**
   * @param integer $userId ID пользователя рассылки
   * @return NotificationsDelivery
   * TRICKY передаем userId чтобы при отравки нотификаций из крона подставлялся юзер для рассылки
   */
  protected function saveNotificationsDelivery($userId = null)
  {
    if ($this->driver->notificationsDelivery) {
      return $this->driver->notificationsDelivery;
    }
    $model = new NotificationsDelivery();
    $model->header = $this->notificationModel->header;
    $model->message = $this->notificationModel->template;
    $model->notification_type = $this->notificationModel->notification_type;
    $model->is_manual = (Yii::$app instanceof Application ? 0 : 1);
    $model->user_id = (Yii::$app instanceof Application ? $userId : Yii::$app->user->id);
    $model->is_news = $this->notificationModel->is_news;
    $model->is_important = $this->notificationModel->is_important;
    $model->from_module_id = $this->notificationModel->module_id;
    $model->event = $this->notificationModel->event;
    $model->emails = $this->notificationModel->emails;
    $model->setRolesArray($this->notificationModel->getRolesAsArray());
    $model->save();
    $this->driver->setNotificationsDelivery($model);
    return $model;
  }

  private function initDriver()
  {
    $driverImplementation = null;
    switch ($this->notificationModel->getNotificationType()) {
      case Notification::NOTIFICATION_TYPE_BROWSER:
        $driverImplementation = Browser::class;
        break;

      case Notification::NOTIFICATION_TYPE_EMAIL:
        $driverImplementation = Email::class;
        break;

      case Notification::NOTIFICATION_TYPE_TELEGRAM:
        $driverImplementation = Telegram::class;
        break;

      case Notification::NOTIFICATION_TYPE_PUSH:
        $driverImplementation = Push::class;
        break;

      default:
        throw new IncorrectDriverTypeException;
    }

    $this->driver = Yii::createObject([
      'class' => $driverImplementation,
      'event' => $this->event,
      'notificationModel' => $this->notificationModel
    ]);
  }

}