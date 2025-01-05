<?php

namespace mcms\statistic\components\subid\handlers;

use mcms\statistic\components\subid\BaseHandler;
use Yii;
use yii\db\Query;
use kak\clickhouse\Query as ClickhouseQuery;
use yii\helpers\ArrayHelper;

class ClickhouseImportHits extends BaseHandler
{
  public function run()
  {
    $minId = (new ClickhouseQuery())
      ->from('hits')
      ->max('hit_id', Yii::$app->clickhouse)
    ;

    $maxId = (new Query)
      ->from('hits')
      ->max('id')
    ;

    $this->log("MinId: {$minId}; MaxId: {$maxId} ");
    if ((int)$minId === (int) $maxId) {
      $this->log("Nothing to sync");
      return ;
    }

    $minId = 312919555;
    $page = 0;
    $limit = 10000;
    $csvInserted = 0;
    $maxRows = 100000;

    $csvPath = Yii::getAlias('@runtime/hits_import.csv');

    $ip = ArrayHelper::getValue(Yii::$app->params, ['clickhouse', 'host']);
    $user = ArrayHelper::getValue(Yii::$app->params, ['clickhouse', 'user']);
    $password = ArrayHelper::getValue(Yii::$app->params, ['clickhouse', 'password']);
    $db = ArrayHelper::getValue(Yii::$app->params, ['clickhouse', 'db']);

    $insertString = strtr('--query="INSERT INTO :db.hits FORMAT CSV" --host :ip --user :user --password :password', [
      ':db' => $db,
      ':ip' => $ip,
      ':user' => $user,
      ':password' => $password,
    ]);

    $execString = "cat {$csvPath} | clickhouse-client {$insertString}";

    $fp = fopen($csvPath, 'w');

    while(true) {

      $query = (new Query())
        ->select([
          'id' => 'h.id',
          'h.time',
          'h.is_tb',
          'h.is_unique',
          'h.traffic_type',
          'h.date',
          'h.hour',
          'h.source_id',
          'h.landing_id',
          'h.operator_id',
          'h.platform_id',
          'h.landing_pay_type_id',
          'sg_1_id' => 'sg_1.id',
          'sg_2_id' => 'sg_2.id'
        ])
        ->from(['h' => 'hits'])
        ->innerJoin(['hp' => 'hit_params'], 'h.id = hp.hit_id')
        ->leftJoin('subid_glossary sg_1', 'sg_1.hash = MD5(hp.subid1)')
        ->leftJoin('subid_glossary sg_2', 'sg_2.hash = MD5(hp.subid2)')
        ->where('h.id > :minId and h.id <= :maxId', [':minId' => $minId, ':maxId' => $maxId])
      ;

      $this->log("$page|");
      $insertQuery = $query
        ->offset($page * $limit)
        ->limit($limit)
        ->all()
      ;

      if (count($insertQuery) === 0) {
        if ($csvInserted > 0) {
          fclose($fp);
          // запись в кликхаус
          exec($execString);
          break;
        }

        fclose($fp);
        break;
      }

      foreach ($insertQuery as $data) {
        fputcsv($fp, $data);
        $csvInserted++;
      }

      if ($csvInserted === 0) {
        fclose($fp);
        break;
      }

      if ($csvInserted >= $maxRows) {
        $csvInserted = 0;
        fclose($fp);
        // запись в кликхаус
        exec($execString);

        // очистка fp
        $fp = fopen($csvPath, 'w');

        $minId = (new ClickhouseQuery())
          ->from('hits')
          ->max('hit_id', Yii::$app->clickhouse)
        ;
        $this->log("New MinId: {$minId};" . PHP_EOL);
        $page = 0;
        continue;
      }

      $page++;
    }

    unlink($csvPath);

    $this->log('Import Hits Done' . PHP_EOL);
  }
}