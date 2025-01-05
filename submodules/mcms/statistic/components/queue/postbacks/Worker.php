<?php

namespace mcms\statistic\components\queue\postbacks;

use mcms\statistic\components\api\ModuleSettings;
use mcms\statistic\components\postbacks\DbFetcher;
use mcms\statistic\components\postbacks\Sender;
use mcms\statistic\Module;
use rgk\queue\PayloadInterface;
use rgk\queue\WorkerInterface;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class Worker
 * @package mcms\statistic\components\queue\postbacks
 */
class Worker implements WorkerInterface
{
  /**
   * Имя канала очереди
   */
  const CHANNEL_NAME = 'postbacks:sender';

  /**
   * @var integer Интервал переотправки постбека
   */
  private $retryInvertval = 60;

  /**
   * @param Payload|PayloadInterface $payload
   * @return bool
   */
  public function work(PayloadInterface $payload)
  {
    if (!$payload->type || !$payload->hitIds) {
      // невалидный Payload, удаляем из очереди

      Yii::warning(
        'Invalid postback payload! ' .
        'Type: ' . $payload->type . ' ' .
        'Hit ids: ' . print_r($payload->hitIds, true) . "\n"
      );

      return true;
    }

    /** @var Module $module */
    $module = Yii::$app->getModule('statistic');
    /** @var ModuleSettings $moduleSettingsApi */
    $moduleSettingsApi = $module->api('moduleSettings');

    $isDuplicatePostback = $moduleSettingsApi->isDuplicatePostback();

    $maxAttempts = $module->settings->getValueByKey(Module::SETTINGS_POSTBACK_MAX_ATTEMPTS) ?: 3;

    $days = $module->settings->getValueByKey(Module::SETTINGS_POSTBACK_MAX_DAYS) ?: 3;
    $timeFrom = strtotime('-' . $days . ' days');

    try {
      if ($isDuplicatePostback) {
        $fetcher = new DbFetcher([
          'type' => $payload->type,
          'hitIds' => $payload->hitIds,
          'isDuplicatePostback' => $isDuplicatePostback,
          'maxAttempts' => $maxAttempts,
          'timeFrom' => $timeFrom,
        ]);

        $sender = new Sender([
          'type' => $payload->type,
          'fetcher' => $fetcher,
          'isDummyExec' => $payload->isDummyExec,
        ]);

        $sender->run();
      }

      $fetcher = new DbFetcher([
        'type' => $payload->type,
        'hitIds' => $payload->hitIds,
        'isDuplicatePostback' => $isDuplicatePostback,
        'maxAttempts' => $maxAttempts,
        'timeFrom' => $timeFrom,
      ]);

      $sender = new Sender([
        'type' => $payload->type,
        'fetcher' => $fetcher,
        'isDummyExec' => $payload->isDummyExec,
      ]);

      $sender->run();
    } catch (\Exception $e) {
      Yii::error($e->getMessage());

      return false;
    }

    /** Неудачно отработавшие постбеки снова кладём в очередь */
    foreach ($sender->getFailed() as $failedPostback) {
      if ($failedPostback['fail_attempt'] < $maxAttempts) {
        try {
          Yii::$app->queue->push(
            static::CHANNEL_NAME,
            new Payload([
              'type' => $payload->type,
              // tricky костылёк для ребиллов, т.к. ребиллы принимают массив transId => hitId
              'hitIds' => [ArrayHelper::getValue($failedPostback, 'trans_id', 0) => $failedPostback['hit_id']],
              'isDummyExec' => $payload->isDummyExec,
            ]),
            $this->retryInvertval
          );
        } catch (\Exception $e) {
          Yii::error(static::CHANNEL_NAME . ' worker exception! ' . $e->getMessage());
        }
      }
    }

    return true;
  }

  /**
   * @inheritdoc
   */
  public function getChannelName()
  {
    return self::CHANNEL_NAME;
  }
}
