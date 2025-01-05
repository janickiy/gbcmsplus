<?php

namespace mcms\promo\commands;

use Yii;
use yii\console\Controller;

/**
 * Обновляем количество хитов у источника
 */
class SourceHitsController extends Controller
{
  public function actionIndex()
  {
    $this->stdout('UPDATE sources hits count...' . PHP_EOL);
    $sql = 'UPDATE sources s, (SELECT hdg.source_id, SUM(hdg.count_hits) AS count_hits FROM hits_day_group hdg
    GROUP BY hdg.source_id) AS h
    SET s.count_hits = h.count_hits
    WHERE s.id = h.source_id';

    Yii::$app->db->createCommand($sql)->execute();
    $this->stdout('UPDATE sources hits done');
  }
}