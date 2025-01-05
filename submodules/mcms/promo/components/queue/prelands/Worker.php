<?php

namespace mcms\promo\components\queue\prelands;

use mcms\promo\components\PrelandDefaultsSync;
use mcms\promo\models\Source;
use rgk\queue\PayloadInterface;
use rgk\queue\WorkerInterface;
use Yii;
use yii\db\Query;
use yii\helpers\Json;

/**
 * Class Worker
 * @package mcms\statistic\components\queue\postbacks
 */
class Worker implements WorkerInterface
{
  /**
   * Имя канала очереди
   */
  const CHANNEL_NAME = 'prelands';


  /**
   * @param Payload|PayloadInterface $payload
   * @return bool
   */
  public function work(PayloadInterface $payload)
  {
    if (!$payload->sourceId && !$payload->streamId && !$payload->userId) {
      // невалидный Payload, удаляем из очереди
      Yii::warning(
        'Invalid preland payload! ' .
        'source_id: ' . $payload->sourceId . ' stream_id: ' . $payload->streamId . 'user_id: ' . $payload->userId . PHP_EOL
      );

      return true;
    }

    try {
      $sources = (new Query())
        ->select([
          'id',
          'user_id',
          'stream_id'
        ])
        ->from(Source::tableName());

      $sources->andFilterWhere(['user_id' => $payload->userId]);
      $sources->andFilterWhere(['stream_id' => $payload->streamId]);
      $sources->andFilterWhere(['id' => $payload->sourceId]);

      foreach ($sources->each() as $source) {
        (new PrelandDefaultsSync([
          'type' => $payload->type,
          'sourceId' => $source['id'],
          'userId' => $source['user_id'],
          'streamId' => $source['stream_id'],
        ]))->run();
      }

    } catch (\Exception $e) {
      Yii::error($e->getMessage() . ' Payload: ' . Json::encode($payload) , __METHOD__);

      return false;
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