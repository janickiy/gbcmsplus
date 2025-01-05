<?php

namespace mcms\statistic\components\subid\handlers;

use mcms\statistic\components\subid\BaseHandler;
use Yii;
use yii\db\Query;
use kak\clickhouse\Query as ClickhouseQuery;
use yii\helpers\ArrayHelper;

class ClickhouseImportSubscriptions extends BaseHandler
{
  private function getMinId()
  {
    return (new ClickhouseQuery())
      ->from('subscriptions')
      ->max('id', Yii::$app->clickhouse)
    ;
  }

  public function run()
  {
    $minId = $this->getMinId();

    $maxId = (new Query())
      ->from('subscriptions')
      ->max('id')
    ;

    $this->log("MinId: {$minId}; MaxId: {$maxId} ");

    if ((int)$minId === (int) $maxId) {
      $this->log("Nothing to sync");
      return ;
    }

    $page = 0;
    $limit = 10000;
    $csvInserted = 0;
    $maxRows = 100000;

    $csvPath = Yii::getAlias('@runtime/subscriptions_import.csv');

    $ip = ArrayHelper::getValue(Yii::$app->params, ['clickhouse', 'host']);
    $user = ArrayHelper::getValue(Yii::$app->params, ['clickhouse', 'user']);
    $password = ArrayHelper::getValue(Yii::$app->params, ['clickhouse', 'password']);
    $db = ArrayHelper::getValue(Yii::$app->params, ['clickhouse', 'db']);

    $insertString = strtr('--query="INSERT INTO :db.subscriptions FORMAT CSV" --host :ip --user :user --password :password', [
      ':db' => $db,
      ':ip' => $ip,
      ':user' => $user,
      ':password' => $password,
    ]);

    $execString = "cat {$csvPath} | clickhouse-client {$insertString}";

    $fp = fopen($csvPath, 'w');
    while(true) {
      $query = (new Query)
        ->select([
          'id',
          'hit_id',
          'trans_id',
          'time',
          'date',
          'hour',
          'landing_id',
          'source_id',
          'operator_id',
          'platform_id',
          'landing_pay_type_id',
          'phone',
          'is_cpa',
          'currency_id',
          'provider_id',
          'is_fake'
        ])
        ->from('subscriptions')
        ->where('id > :minId and id <= :maxId', [':minId' => $minId, ':maxId' => $maxId]);

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

        $minId = $this->getMinId();
        $this->log("New MinId: {$minId};" . PHP_EOL);
        $page = 0;
        continue;
      }

      $page++;
    }

    unlink($csvPath);

    $this->log('Import Subscriptions done' . PHP_EOL);
  }
}