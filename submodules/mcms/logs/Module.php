<?php

namespace mcms\logs;

use mcms\common\event\Event;
use mcms\common\event\EventObject;
use mcms\logs\components\event\Catcher;
use mcms\user\models\User;
use yii\base\BootstrapInterface;
use yii\base\Event as BaseEvent;
use yii\db\ActiveRecord;
use yii\console\Application as ConsoleApplication;
use Yii;
use mcms\notifications\models\BrowserNotification;
use mcms\notifications\models\EmailNotification;
use mcms\logs\models\Logs;
use mcms\support\models\SupportHistory;
use mcms\payments\models\ExchangerCourse;
use mcms\user\models\LoginLog;
use mcms\payments\models\HoldBalanceTransferLog;

/**
 * Class Module
 * @package mcms\logs
 */
class Module extends \mcms\common\module\Module implements BootstrapInterface
{
  const ROOT_USER_ID = User::ROOT_USER_ID;
  public $controllerNamespace = 'mcms\logs\controllers';

  const MODEL_INSERT = "INSERT";
  const MODEL_UPDATE = "UPDATE";
  const MODEL_DELETE = "DELETE";

  const MODEL_LOGS_TABLE_NAME = 'action_logs';

  const SETTINGS_ENABLE_LOG = 'settings.enable_log';

  public function init()
  {
    parent::init();

    if (Yii::$app instanceof ConsoleApplication) {
      $this->controllerNamespace = 'mcms\logs\commands';
    } else {
      $this->modules = [
        'datecontrol' => [
          'class' => \kartik\datecontrol\Module::class
        ]
      ];
    }

    // проверяем, что это не запуск юнит-тестов
    if (!defined('YII_ENV_TEST') || !YII_ENV_TEST) {

      if (!defined('EVENTS_MODEL_LOGS_ATTACHED')) {
        BaseEvent::on(ActiveRecord::class, ActiveRecord::EVENT_AFTER_INSERT, function ($event) {
          $this->insertHistory($event->sender, self::MODEL_INSERT);
        });
        BaseEvent::on(ActiveRecord::class, ActiveRecord::EVENT_BEFORE_UPDATE, function ($event) {
          $this->insertHistory($event->sender, self::MODEL_UPDATE);
        });
        BaseEvent::on(ActiveRecord::class, ActiveRecord::EVENT_BEFORE_DELETE, function ($event) {
          $this->insertHistory($event->sender, self::MODEL_DELETE);
        });
      }
      define('EVENTS_MODEL_LOGS_ATTACHED', true);

    }
  }

  /**
   * @inheritdoc
   */
  public function bootstrap($app)
  {
    // проверяем, что это не запуск юнит-тестов
    if (!defined('YII_ENV_TEST') || !YII_ENV_TEST) {
      Yii::$app->on(Event::EVENT_CAPTURED, function (EventObject $eventObject) {
        $catcher = new Catcher();
        $catcher->catchEvent($eventObject);
      });
    }
  }

  /**
   * @param $model ActiveRecord
   * @param $type
   * @throws \yii\db\Exception
   */
  private function insertHistory($model, $type)
  {
    if (self::isIgnoreLogClass($model::class)) {
      return;
    }

    $oldAttributes = $type == self::MODEL_INSERT ? [] : $model->getOldAttributes();
    $newAttributes = $type == self::MODEL_DELETE ? [] : $model->getAttributes();

    if (empty($oldAttributes) && empty($newAttributes)) {
      return;
    }

    ksort($oldAttributes);
    ksort($newAttributes);

    if (
      empty($model->getDirtyAttributes())
      && !in_array($type, [self::MODEL_INSERT, self::MODEL_DELETE])
    ) return;

    $pk = $model->getPrimaryKey();

    try {

      Yii::$app->clickhouse->createCommand(null)
        ->insert(self::MODEL_LOGS_TABLE_NAME, [
          'EventTime' => date('Y-m-d H:i:s'),
          'EventClass' => $model::class,
          'EventType' => $type,
          'PK' => is_array($pk) ? implode(',', $pk) : $pk,
          'Old' => $oldAttributes ? json_encode($oldAttributes) : '',
          'New' => $newAttributes ? json_encode($newAttributes) : '',
          'UserID' => isset(Yii::$app->user->id) ? Yii::$app->user->id : self::ROOT_USER_ID
        ])
        ->execute();

    } catch (\Exception $e) {

      Yii::warning(
        'MODEL_LOGS UNAVAILABLE' . PHP_EOL .
        $e->getMessage() . PHP_EOL .
        $e->getTraceAsString()
      );

    }
  }

  /**
   * @param $class
   * @return mixed
   */
  private static function isIgnoreLogClass($class)
  {
    return in_array($class, [
      BrowserNotification::class,
      EmailNotification::class,
      Logs::class,
      SupportHistory::class,
      ExchangerCourse::class,
      LoginLog::class,
      HoldBalanceTransferLog::class
    ], true);
  }

  /**
   * @return array|mixed
   */
  public function isLogEnabled()
  {
    return $this->settings->getValueByKey(self::SETTINGS_ENABLE_LOG);
  }
}
