<?php

namespace mcms\notifications\components\event\driver;

use mcms\notifications\models\Notification;
use mcms\notifications\models\NotificationsDelivery;
use mcms\notifications\models\NotificationsIgnore;
use mcms\user\models\User;
use mcms\user\models\UserParam;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Model;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

abstract class AbstractDriver extends BaseObject implements DriverInterface
{

  /** @var \mcms\common\event\Event */
  public $event;

  /** @var \mcms\notifications\models\Notification */
  public $notificationModel;

  private $originalOwner;

  private $_notificationsDelivery;

  public function init()
  {
    parent::init();
    $this->originalOwner = $this->event->owner;
  }

  private function replaceText($attribute, array $replacements)
  {
    if (array_key_exists($attribute, $replacements)) {
      return ArrayHelper::getValue($replacements, $attribute);
    }
    $replacements = array_filter($replacements, function($replacement) {
      return !($replacement instanceof Model) && !is_array($replacement);
    });
    return strtr($attribute, $replacements);
  }

  protected function injectOwner($user)
  {
    $this->event->setOwner($user);
    return $this;
  }

  protected function translate()
  {
    $args = func_get_args();
    $translated = [];
    $originalLanguage = Yii::$app->language;
    foreach ($args as $k => $funcArg) {
      $translated[$k] = [];
      foreach (ArrayHelper::toArray($funcArg) as $lang => $str) {
        Yii::$app->language = $lang;
        $translated[$k][$lang] = $this->replaceText($str, $this->event->getReplacements());
      }
    }
    Yii::$app->language = $originalLanguage;
    return $translated;
  }

  /**
   * Отправить уведомление
   *
   * TRICKY Аргумент $user может быть isNewRecord.
   * Этот костыль используется для отправки уведомлений на дополнительные email-адреса.
   * Пример использования костыля @see \mcms\notifications\components\event\Handler::sendAdditionalEmails
   * На момент написания комментария такой костыль поддерживал только email-драйвер.
   * Для поддержки этого костыля нужно переопределить метод проверки @see checkUserIsset
   * По умолчанию данный костыль отключен, что бы не было трудноотлавливаемых багов из-за отсутствия $user->id
   *
   * @param User $receiver
   * @return bool
   */
  public function send(User $receiver)
  {
    if (!$this->checkUserIsset($receiver)) {
      throw new InvalidArgumentException('Отправка уведомления для несуществующего пользователя не поддерживается. '
        . get_class($this) . ' User: ' . Json::encode($receiver->attributes));
    }

    if ($receiver->id) {
      // tricky системные сообщения должны отправлять и неактивным пользователям (модерация, регистрация, ...)
      if (!$receiver->isActive() && !$this->notificationModel->is_system) return false;
      // tricky если это не тест и не важная новость проверяем необходимость отправки уведомления
      if (!$this->isImportantNews() && !$this->notificationModel->isTest) {
        // проверяем не запретил ли пользователь отправляемое уведомление
        if ($this->checkIgnore($receiver->id)) return false;
        $userParams = $receiver->getParams();
        // Можно ли отправлять новости
        if (!$this->checkIsNews($userParams)) return false;
        // Можно ли отправлять системные уведомления
        if (!$this->checkIsSystem($userParams)) return false;
        // проверяем доступность категории
        if (!$this->checkCategories($userParams)) return false;
      }
    }

    try {
      return $this->sendHandler($receiver);
    } catch (\Exception $e) {
      Yii::debug($e->getMessage(), __METHOD__);
      return false;
    }
  }

  /**
   * Проверка существования пользователя
   * Больше инфы @see \mcms\notifications\components\event\driver\Email::checkUserIsset
   * @param User $user
   * @return bool
   */
  protected function checkUserIsset(User $user)
  {
    return !$user->isNewRecord;
  }

  /**
   * Можно ли отправлять новости
   * @param UserParam $userParams
   * @return string
   */
  abstract protected function getNotifyNews(UserParam $userParams);

  /**
   * Можно ли отправлять системные
   * @param UserParam $userParams
   * @return string
   */
  abstract protected function getNotifySystem(UserParam $userParams);

  /**
   * Разрешенные категории
   * @param UserParam $userParams
   * @return array
   */
  abstract protected function getNotifyCategories(UserParam $userParams);

  /**
   * Проверка, что это важная новость
   * @return bool
   */
  protected function isImportantNews()
  {
    return $this->notificationModel->is_important && $this->notificationModel->is_news;
  }

  /**
   * @return \mcms\modmanager\models\Module
   */
  protected function getModule()
  {
    return $this->notificationModel->getModule()->one();
  }

  /**
   * @param UserParam $userParams
   * @return bool
   */
  protected function checkIsSystem(UserParam $userParams)
  {
    return $this->getNotifySystem($userParams) || !$this->notificationModel->is_system;
  }

  /**
   * @param UserParam $userParams
   * @return bool
   */
  protected function checkIsNews(UserParam $userParams)
  {
    return $this->getNotifyNews($userParams) || !$this->notificationModel->is_news;
  }

  /**
   * @param UserParam $userParams
   * @return bool
   */
  protected function checkCategories(UserParam $userParams)
  {
    return in_array($this->notificationModel->module_id, $this->getNotifyCategories($userParams));
  }

  /**
   * Проверяем не запретил ли пользователь это уведомление
   * @param $userId
   * @return bool
   */
  protected function checkIgnore($userId)
  {
    // если принудительно отправлено, не игнорируем
    if ($this->notificationModel->isForceSend) {
      return false;
    }

    $notificationId = Notification::fetchId($this->notificationModel);

    return NotificationsIgnore::find()->where([
      'user_id' => $userId,
      'notification_id' => $notificationId,
    ])->exists();
  }

  public function setNotificationsDelivery(NotificationsDelivery $model)
  {
    $this->_notificationsDelivery = $model;
  }

  public function getNotificationsDelivery()
  {
    return $this->_notificationsDelivery;
  }
}