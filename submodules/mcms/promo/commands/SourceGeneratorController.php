<?php


namespace mcms\promo\commands;

use mcms\promo\models\Source;
use yii\console\Controller;
use Yii;
use yii\db\Query;

/**
 * Class SourceGeneratorController
 * @package mcms\promo\commands
 */
class SourceGeneratorController extends Controller
{

  public function actionIndex(array $operators, $userId, $count = 1)
  {
    $db = Yii::$app->db;
    $this->stdout(PHP_EOL . "Start" . PHP_EOL);

    $stream = (new Query())
      ->select('id')
      ->from('streams')
      ->where(['user_id' => $userId])
      ->one();

    $banner = (new Query())
      ->select('id')
      ->from('banners')
      ->where(['is_disabled' => 0])
      ->one();

    $landings_ = (new Query())
      ->select('landing_id, operator_id')
      ->from('landing_operators')
      ->where(['operator_id' => $operators])
      ->groupBy('operator_id')
      ->all();
    $landings = [];

    foreach($landings_ as $landing) {
      $landings[$landing['operator_id']] = $landing['landing_id'];
    }

    for ($i=0;$i<$count;$i++) {
      $created = time();
      $name = 'source_' . $i;
      $hash = substr(md5($name . $created), 0, 10);


      $db->createCommand(strtr('INSERT INTO :table (
        hash, user_id, default_profit_type, ads_type, status, source_type, name, stream_id, created_at, updated_at
      ) VALUES (
        ":hash", :user_id, :default_profit_type, :ads_type, :status, :source_type, ":name", :stream_id, :created_at, :updated_at
      )',
        [
          ':table' => Source::tableName(),
          ':hash' => $hash,
          ':user_id' => $userId,
          ':default_profit_type' => 1,
          ':ads_type' => 3,
          ':status' => 1,
          ':source_type' => 1,
          ':name' => $name,
          ':stream_id' => $stream['id'],
          ':created_at' => $created,
          ':updated_at' => $created
        ]
      ))->execute();

      $sourceId = $db->lastInsertID;

      $this->stdout($hash . PHP_EOL);

      $db->createCommand(strtr('INSERT INTO :table (
        source_id, banner_id
      ) VALUES (
        :source_id, :banner_id
      )',
        [
          ':table' => 'sources_banners',
          ':source_id' => $sourceId,
          ':banner_id' => $banner['id'],
        ]
      ))->execute();

      foreach ($operators as $operatorId) {
        $db->createCommand(strtr('INSERT INTO :table (
        source_id, profit_type, operator_id, landing_id, landing_choose_type
      ) VALUES (
        :source_id, :profit_type, :operator_id, :landing_id, :landing_choose_type
      )',
          [
            ':table' => 'sources_operator_landings',
            ':source_id' => $sourceId,
            ':profit_type' => 1,
            ':operator_id' => $operatorId,
            ':landing_id' => $landings[$operatorId],
            ':landing_choose_type' => 1,
          ]
        ))->execute();
      }

    }

    $this->stdout(PHP_EOL . "End" . PHP_EOL);

  }
}
