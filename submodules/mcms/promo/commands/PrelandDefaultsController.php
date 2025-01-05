<?php

namespace mcms\promo\commands;

use mcms\promo\components\PrelandDefaultsSync;
use mcms\promo\models\PrelandDefaults;
use mcms\promo\models\Source;
use yii\console\Controller;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Удаляет у источников операторов для прелендов, для которых уже нет правил, и добавляет новые
 */
class PrelandDefaultsController extends Controller
{
  public function actionIndex()
  {
    $sources = (new Query())
      ->select([
        'id',
        'user_id',
        'stream_id',
      ])
      ->from(Source::tableName())
      ->where(['status' => Source::STATUS_APPROVED])
      ->each();

    foreach ($sources as $source) {
      $this->stdout('Add pleland operators to source ' . $source['id'] . PHP_EOL, Console::BOLD);
      (new PrelandDefaultsSync([
        'type' => [PrelandDefaults::TYPE_ADD, PrelandDefaults::TYPE_OFF],
        'sourceId' => $source['id'],
        'userId' => $source['user_id'],
        'streamId' => $source['stream_id'],
      ]))->run();
    }
  }
}