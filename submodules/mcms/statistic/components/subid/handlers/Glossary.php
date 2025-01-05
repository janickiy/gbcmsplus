<?php

namespace mcms\statistic\components\subid\handlers;


use mcms\statistic\components\subid\BaseHandler;
use Yii;

/**
 * Заполнение справочника subid
 */
class Glossary extends BaseHandler
{
  const COUNT = 10000;

  /**
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\db\Exception
   */
  public function run()
  {
    $hitsRange = Yii::$app->db->createCommand('SELECT MAX(id) AS maxHit, MIN(id) AS minHit FROM hits WHERE date >= :dateFrom AND date <= :dateTo')
      ->bindValue(':dateFrom', $this->cfg->getDateFrom(), \PDO::PARAM_STR)
      ->bindValue(':dateTo', $this->cfg->getDateTo(), \PDO::PARAM_STR)
      ->queryOne();

    if (!$hitsRange) {
      return;
    }

    for ($minHit = $hitsRange['minHit']; $minHit <= $hitsRange['maxHit']; $minHit += self::COUNT) {
      $maxHit = $minHit + self::COUNT - 1;
      if ($maxHit > $hitsRange['maxHit']) {
        $maxHit = $hitsRange['maxHit'];
      }

      $this->insert($minHit, $maxHit);
    }
  }

  protected function insert($minHit, $maxHit)
  {
    Yii::$app->db->createCommand("
        /** @lang MySQL */
        INSERT INTO `subid_glossary`
        (`value`, `hash`, `last_touched_at`)
        SELECT * FROM (
                SELECT hp.subid1 AS value, MD5(hp.subid1) AS hash, UNIX_TIMESTAMP() as last_touched_at
                FROM hit_params hp
                WHERE hp.subid1 IS NOT NULL
                      AND hp.subid1 <> ''
                      AND hit_id >= :minHit
                      AND hit_id <= :maxHit
                UNION
                SELECT hp.subid2 AS value, MD5(hp.subid2) AS hash, UNIX_TIMESTAMP() as last_touched_at
                FROM hit_params hp
                WHERE hp.subid2 IS NOT NULL
                      AND hp.subid2 <> ''
                      AND hit_id >= :minHit
                      AND hit_id <= :maxHit
              ) sub
        ON DUPLICATE KEY UPDATE
          last_touched_at = VALUES(last_touched_at)")
      ->bindValue(':minHit', $minHit, \PDO::PARAM_INT)
      ->bindValue(':maxHit', $maxHit, \PDO::PARAM_INT)
      ->execute();
  }
}