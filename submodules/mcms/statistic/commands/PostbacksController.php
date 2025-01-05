<?php

namespace mcms\statistic\commands;

use mcms\common\helpers\ArrayHelper;
use mcms\statistic\components\api\ModuleSettings;
use mcms\statistic\components\postbacks\DbFetcher;
use mcms\statistic\components\postbacks\Sender;
use mcms\statistic\components\queue\postbacks\Payload;
use mcms\statistic\components\queue\postbacks\Worker;
use mcms\statistic\models\Complain;
use mcms\statistic\models\Postback;
use mcms\statistic\Module;
use yii\helpers\Console;
use yii\console\Controller;
use Yii;

/**
 * Class PostbacksController
 * @package mcms\statistic\commands
 */
class PostbacksController extends Controller
{
  /**
   * @var bool
   */
  protected $isDuplicatePostback;

  /**
   * @var array
   */
  protected $allowedPostbackTypes;

  /**
   * @var int
   */
  public $timeFrom;

  /**
   * @var string|int
   */
  public $timeTo = 'now';

  /**
   * @var int
   */
  public $maxAttempts;

  /**
   * @var int
   */
  public $dummyExec = false;

  /**
   * При переданном true не будет использовать очереди
   * @var bool
   */
  public $withoutQueue = true;

  /**
   * @inheritdoc
   */
  public function options($actionID)
  {
    return ['timeFrom', 'timeTo', 'dummyExec', 'withoutQueue'];
  }

  /**
   * @inheritdoc
   */
  public function actionIndex()
  {
    /** @var Module $statisticModule */
    $statisticModule = Yii::$app->getModule('statistic');

    /** @var ModuleSettings $moduleSettingsApi */
    $moduleSettingsApi = $statisticModule->api('moduleSettings');

    $this->isDuplicatePostback = $moduleSettingsApi->isDuplicatePostback();

    $this->allowedPostbackTypes = [Postback::ORIGINAL];
    if ($this->isDuplicatePostback) {
      $this->allowedPostbackTypes[] = Postback::DUPLICATE;
    }

    $this->maxAttempts = $statisticModule->settings->getValueByKey(Module::SETTINGS_POSTBACK_MAX_ATTEMPTS) ?: 3;

    $days = $statisticModule->settings->getValueByKey(Module::SETTINGS_POSTBACK_MAX_DAYS) ?: 3;
    $this->timeFrom = strtotime('-' . $days . ' days');
    $this->timeTo = is_int($this->timeTo) ? $this->timeTo : strtotime($this->timeTo);

    try {
      $this->stdout('=== subscriptions BEGIN' . "\n");

      $this->push(Sender::TYPE_SUBSCRIPTION);

      $this->stdout('=== subscriptionRebills BEGIN' . "\n");

      $this->push(Sender::TYPE_REBILL);

      $this->stdout('=== onetimeSubscriptions BEGIN' . "\n");

      $this->push(Sender::TYPE_ONETIME_SUBSCRIPTION);

      $this->stdout('=== soldSubscriptions BEGIN' . "\n");

      $this->push(Sender::TYPE_SUBSCRIPTION_SELL);

      $this->stdout('=== subscriptionOffs BEGIN' . "\n");

      $this->push(Sender::TYPE_SUBSCRIPTION_OFF);

      // TODO пока отключили ПБ по жалобам чтоб не шокировать партнеров
      // TRICKY отправка жалоб с задерждой только через очереди
      $this->stdout('=== complains BEGIN' . "\n");
      $this->queuePush(Sender::TYPE_COMPLAIN);
    } catch (\Exception $e) {
      $this->stdout('Error occurred: ' . $e->getMessage() . "\n", Console::FG_RED);
    }
  }

  /**
   * @param $type
   */
  protected function push($type)
  {
    return $this->withoutQueue
      ? $this->dbPush($type)
      : $this->queuePush($type);
  }

  /**
   * @param $type
   */
  protected function dbPush($type)
  {
    foreach ($this->allowedPostbackTypes as $postbackType) {
      $fetcher = new DbFetcher([
        'type' => $type,
        'isDuplicatePostback' => Postback::isDuplicateType($postbackType),
        'timeFrom' => $this->timeFrom,
        'timeTo' => $this->timeTo,
        'maxAttempts' => $this->maxAttempts,
      ]);

      (new Sender([
        'type' => $type,
        'isDummyExec' => $this->dummyExec,
        'timeFrom' => $this->timeFrom,
        'timeTo' => $this->timeTo,
        'maxAttempts' => $this->maxAttempts,
        'fetcher' => $fetcher
      ]))->run();
    }
  }

  /**
   * @param $type
   * @param $delay
   */
  protected function queuePush($type, $delay = 0)
  {
    foreach ($this->allowedPostbackTypes as $postbackType) {
      $fetcher = new DbFetcher([
        'type' => $type,
        'isDuplicatePostback' => Postback::isDuplicateType($postbackType),
        'timeFrom' => $this->timeFrom,
        'timeTo' => $this->timeTo,
        'maxAttempts' => $this->maxAttempts,
      ]);

      foreach ($fetcher->batch() as $data) {
        $hitIds = array_map(function ($value) {
          return $value['hit_id'];
        }, $data);

        if ($type === Sender::TYPE_COMPLAIN) {
          $delay = Complain::getPostbackDelay();
        }

        try {
          Yii::$app->queue->push(
            Worker::CHANNEL_NAME,
            new Payload([
              'hitIds' => $hitIds,
              'type' => $type,
              'isDummyExec' => $this->dummyExec,
            ]),
            $delay
          );
        } catch (\Exception $e) {
          Yii::error(Worker::CHANNEL_NAME . ' worker exception! ' . $e->getMessage());
        }
      }
    }
  }
}
