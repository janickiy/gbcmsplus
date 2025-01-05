<?php

namespace mcms\statistic\commands;

use Yii;
use yii\console\Controller;
use yii\db\Connection;
use yii\db\Expression;
use yii\db\Query;
use kak\clickhouse\Query as ClickhouseQuery;

class ClickhouseImportController extends Controller
{

  public function actionIndex()
  {
    $this->actionHits();
    $this->actionRebills();
  }

  public function actionHits()
  {
    $this->stdout('Import hits' . PHP_EOL);

    $minId = (new ClickhouseQuery())
      ->from('hits')
      ->max('hit_id', Yii::$app->clickhouse)
    ;

    $maxId = (new Query)
      ->from('hits')
      ->max('id')
    ;

    $page = 0;
    $limit = 10000;
    $csvInserted = 0;
    $fp = fopen('hits_import.csv', 'w');
    while(true) {

      $query = (new Query())
        ->select([
          'h.id',
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
          new Expression('IFNULL(hp.subid1, "")'),
          new Expression('IFNULL(hp.subid2, "")')
        ])
        ->from(['h' => 'hits'])
        ->innerJoin(['hp' => 'hit_params'], 'h.id = hp.hit_id')
        ->where('h.id > :minId and h.id <= :maxId', [':minId' => $minId, ':maxId' => $maxId])
      ;

      $this->stdout("$page|");
      $insertQuery = $query
        ->offset($page * $limit)
        ->limit($limit)
        ->all()
      ;

      if (count($insertQuery) === 0) {
        if ($csvInserted > 0) {
          fclose($fp);
          // запись в кликхаус
          $execString = "cat /var/www/clients/client1/web2/web/hits_import.csv | clickhouse-client --query=\"INSERT INTO wapclick.hits FORMAT CSV\" --host 147.135.239.166 --user wap --password TjgumoiUh2TiQm7aq1rZL";
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

      if ($csvInserted >= 500000) {
        $csvInserted = 0;
        fclose($fp);
        // запись в кликхаус
        $execString = "cat /var/www/clients/client1/web2/web/hits_import.csv | clickhouse-client --query=\"INSERT INTO wapclick.hits FORMAT CSV\" --host 147.135.239.166 --user wap --password TjgumoiUh2TiQm7aq1rZL";
        exec($execString);

        // очистка fp
        $fp = fopen('hits_import.csv', 'w');

        $minId = (new ClickhouseQuery())
          ->from('hits')
          ->max('hit_id', Yii::$app->clickhouse)
        ;
        $this->stdout("New MinId: {$minId};" . PHP_EOL);
        $page = 0;
        continue;
      }

      $page++;
    }

    unlink('hits_import.csv');

    $this->stdout('Import Hits Done' . PHP_EOL);
  }

  public function actionRebills()
  {
    $this->stdout('Import Rebills' . PHP_EOL);

    $minId = (new ClickhouseQuery())
      ->from('rebills')
      ->max('id', Yii::$app->clickhouse)
    ;

    $maxId = (new Query())
      ->from('subscription_rebills')
      ->max('id')
    ;

    $this->stdout("MinId: {$minId}; MaxId: {$maxId} " . PHP_EOL);

    $query = (new Query())
      ->select([
        'id',
        'hit_id',
        'trans_id',
        'time',
        'date',
        'hour',
        'default_profit',
        'default_profit_currency',
        'currency_id',
        'real_profit_rub',
        'real_profit_eur',
        'real_profit_usd',
        'reseller_profit_rub',
        'reseller_profit_eur',
        'reseller_profit_usd',
        'profit_rub',
        'profit_eur',
        'profit_usd',
        'landing_id',
        'source_id',
        'old_source_id' => 'IFNULL(old_source_id, 0)',
        'operator_id',
        'platform_id',
        'landing_pay_type_id',
        'is_cpa',
        'provider_id',
      ])
      ->from('subscription_rebills')
      ->where('id > :minId and id <= :maxId', [':minId' => $minId, ':maxId' => $maxId])
    ;

    $page = 0;
    $limit = 1000;

    while(true) {
      $this->stdout("$page|");
      $insertData = $query
        ->offset($page * $limit)
        ->limit($limit)
        ->all()
      ;

      if (count($insertData) === 0) break;

      Yii::$app->clickhouse->createCommand()
        ->batchInsert('rebills', [
          'id',
          'hit_id',
          'trans_id',
          'timestamp',
          'date',
          'hour',
          'default_profit',
          'default_profit_currency',
          'currency_id',
          'real_profit_rub',
          'real_profit_eur',
          'real_profit_usd',
          'reseller_profit_rub',
          'reseller_profit_eur',
          'reseller_profit_usd',
          'profit_rub',
          'profit_eur',
          'profit_usd',
          'landing_id',
          'source_id',
          'old_source_id',
          'operator_id',
          'platform_id',
          'landing_pay_type_id',
          'is_cpa',
          'provider_id',
        ], $insertData)->execute();

      $page++;
    }

    $this->stdout('Import Rebills Done' . PHP_EOL);
  }
}
