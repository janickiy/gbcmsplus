<?php

namespace mcms\notifications;

use mcms\common\helpers\ArrayHelper;
use mcms\notifications\components\events\TelegramAutoUnsubscribeEvent;
use mcms\notifications\components\telegram\Api;
use Yii;
use mcms\common\event\Event;
use mcms\common\event\EventObject;
use mcms\notifications\components\event\Catcher;
use yii\console\Application as ConsoleApplication;
use yii\helpers\Url;

class Module extends \mcms\common\module\Module
{
  public $controllerNamespace = 'mcms\notifications\controllers';
  public $name;
  public $menu;
  public $pushApiKey;

  const SETTINGS_NOTIFY_EMAIL = 'settings.notify_email';
  const SETTINGS_NOTIFY_BROWSER = 'settings.notify_browser';

  const SETTINGS_ADMIN_EMAIL = 'settings.admin_email';
  const SETTINGS_INFO_EMAIL = 'settings.info_email';
  const SETTINGS_NOREPLY_EMAIL = 'settings.noreply_email';
  const SETTINGS_SUPPORT_EMAIL = 'settings.support_email';
  const SETTINGS_TELEGRAM_BOT_NAME = 'settings.telegram_bot_name';
  const SETTINGS_TELEGRAM_BOT_TOKEN = 'settings.telegram_bot_token';
  const SETTINGS_PUSH_ICON = 'settings.push_icon';

  const SCENARIO_ENABLE = 'enable';
  const SCENARIO_DISABLE = 'disable';

  const FN_QUERY_PARAM = 'fn';

  /**
   * @inheritDoc
   */
  public function init()
  {
    parent::init();

    if (Yii::$app instanceof ConsoleApplication) {
      $this->controllerNamespace = 'mcms\notifications\commands';
    }

    \Yii::$app->on(Event::EVENT_CAPTURED, function(EventObject $eventObject) {
      /** @var \mcms\notifications\components\event\Catcher $catcher */
      $catcher = \Yii::$container->get('mcms\notifications\components\event\Catcher');
      $catcher->catchEvent($eventObject->event);
    });

  }

  public function adminEmail()
  {
    return $this->settings->getValueByKey(self::SETTINGS_ADMIN_EMAIL);
  }

  public function infoEmail()
  {
    return $this->settings->getValueByKey(self::SETTINGS_INFO_EMAIL);
  }

  public function supportEmail()
  {
    return $this->settings->getValueByKey(self::SETTINGS_SUPPORT_EMAIL);
  }

  public function noreplyEmail()
  {
    return $this->settings->getValueByKey(self::SETTINGS_NOREPLY_EMAIL);
  }

  /**
   * Получить токен для Telegram бота
   * @return string|null
   */
  public function getTelegramBotToken()
  {
    return $this->settings->getValueByKey(self::SETTINGS_TELEGRAM_BOT_TOKEN);
  }

  /**
   * Получить название Telegram бота
   * @return string|null
   */
  public function getTelegramBotName()
  {
    return $this->settings->getValueByKey(self::SETTINGS_TELEGRAM_BOT_NAME);
  }

  /**
   * Настроен ли Телеграм
   * @return bool
   */
  public function isTelegramConfigured()
  {
    return $this->getTelegramBotToken() && $this->getTelegramBotName();
  }

  /**
   * Получить иконку для Push уведомлений
   * @return string|null
   */
  public function getPushIconUrl()
  {
    return $this->settings->offsetGet(self::SETTINGS_PUSH_ICON)->getUrl();
  }

}