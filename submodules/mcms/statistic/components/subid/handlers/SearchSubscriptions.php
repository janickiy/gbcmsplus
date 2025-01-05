<?php

namespace mcms\statistic\components\subid\handlers;

use mcms\statistic\components\subid\BaseHandler;
use Yii;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Class SearchSubscriptions
 * @package mcms\statistic\components\cron\handlers
 */
class SearchSubscriptions extends BaseHandler
{
  public function run()
  {
    $lastTimeFrom = $this->cfg->getTimeFrom();
    /** обновляем поля из hit_params */
    $query = (new Query())
      ->select(['hit_params.hit_id', 'hit_params.ip', 'sg1.id as subid1_id', 'sg2.id as subid2_id'])
      ->from('search_subscriptions ss')
      ->innerJoin('hit_params', 'hit_params.hit_id = ss.hit_id')
      ->leftJoin('subid_glossary sg1', "MD5(IFNULL(`hit_params`.subid1, '')) = sg1.hash")
      ->leftJoin('subid_glossary sg2', "MD5(IFNULL(`hit_params`.subid2, '')) = sg2.hash")
      ->where(['>=', 'ss.last_time', $lastTimeFrom]);

    foreach ($query->batch() as $partData) {
      $sql = Yii::$app->db->createCommand()->batchInsert(
          'search_subscriptions',
          ['hit_id', 'ip', 'subid1_id', 'subid2_id'],
          $partData
        )->rawSql . ' ON DUPLICATE KEY UPDATE ip = VALUES(ip), subid1_id = VALUES(subid1_id), subid2_id = VALUES(subid2_id)';
  
      //Console::output($sql);
      Yii::$app->db->createCommand($sql)->execute();
    }
  }
}