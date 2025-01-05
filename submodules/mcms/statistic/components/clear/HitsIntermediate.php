<?php

namespace mcms\statistic\components\clear;
use mcms\common\output\ConsoleOutput;
use Yii;

/**
 *
 *
 * Class HitsIntermediate
 * @package mcms\statistic\components\clear
 */
class HitsIntermediate extends AbstractCleaner
{

  /** @var  int */
  public $daysKeepTb = 14;
  public $daysKeepHits = 30;

  private $_scriptTime;
  private $_keepTbFrom;
  private $_keepHitsFrom;

  public function init()
  {
    parent::init();
    $this->setLogger(new ConsoleOutput()); // логгер по-умолчанию в консоль

    /* Создаем переменную со временем, чтобы не было пересечений id из-за разного времени, так как у нас 3 запроса */
    $this->_scriptTime = time();
    $this->_keepTbFrom = $this->_scriptTime - $this->daysKeepTb * 86400;
    $this->_keepHitsFrom = $this->_scriptTime - $this->daysKeepHits * 86400;
  }


  public function run()
  {
    $this->log("SCRIPT STARTED WITH NEXT DATA: ");
    $this->log("
      scriptTimeUnixTime: ". $this->_scriptTime . "
      scriptTime: ". date('H:i:s d.m.Y', $this->_scriptTime) . "
      keep tb from: ". date('H:i:s d.m.Y', $this->_keepTbFrom) . "
      keep hits from: ". date('H:i:s d.m.Y', $this->_keepHitsFrom) . "
      days keep tb: " . $this->daysKeepTb . "
      days keep hits: " . $this->daysKeepHits . "
    ");

    $maxHitId =  $this->db->createCommand('SELECT MAX(id) FROM hits')->queryScalar();
    $this->log('Max hit id: ' . $maxHitId);

    $scriptTime = $this->_scriptTime;
    $keepTb = $this->_keepTbFrom;
    $keepHits = $this->_keepHitsFrom;

    $this->log('Create table hits_intermediate');
    $sql = <<<SQL
/* HITS */
CREATE TABLE `hits_intermediate` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`is_unique` TINYINT(1) UNSIGNED NOT NULL DEFAULT "0",
	`is_tb` TINYINT(1) UNSIGNED NOT NULL DEFAULT "0",
	`time` INT(10) UNSIGNED NOT NULL,
	`date` DATE NOT NULL,
	`hour` TINYINT(1) UNSIGNED NOT NULL,
	`operator_id` MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT "0",
	`landing_id` MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT "0",
	`source_id` MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT "0",
	`platform_id` MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT "0",
	`landing_pay_type_id` TINYINT(1) UNSIGNED NOT NULL DEFAULT "0",
	`is_cpa` TINYINT(1) UNSIGNED NULL DEFAULT "0",
	`traffic_type` TINYINT(1) UNSIGNED NULL DEFAULT "0",
	PRIMARY KEY (`id`),
	INDEX `hits_group_by_hour_$scriptTime` (`date`, `source_id`, `landing_id`, `operator_id`, `platform_id`, `hour`, `landing_pay_type_id`, `is_cpa`, `is_unique`, `is_tb`)
)
COLLATE="utf8_unicode_ci"
ENGINE=InnoDB;
SQL;
    $this->db->createCommand($sql)->execute();


    $this->log('Переносим данные по трафикбеку за последние ' . $keepTb);
    $sql = <<<SQL
/* Переносим данные по трафикбеку за последние 2 недели */
INSERT INTO hits_intermediate
SELECT h.*
FROM hits h
WHERE h.is_tb > 0
	AND h.time > :keepTb;
SQL;
    $this->db->createCommand($sql, [':keepTb' => $keepTb])->execute();
    $this->log('Готово.');

    $this->log('Переносим все данные за последние ' . $keepHits . ' дней, кроме tb');
    $sql = <<<SQL
/* переносим все данные за последние 2 месяца, кроме tb */
INSERT INTO hits_intermediate
SELECT h.*
FROM hits h
WHERE h.is_tb = 0
	AND h.time > :keepHits;
SQL;
    $this->db->createCommand($sql, [':keepHits' => $keepHits])->execute();
    $this->log('Готово');

    $this->log('Переносим хиты с подписками старше ' . $keepHits . ' дней');
    $sql = <<<SQL
/* Переносим хиты с подписками старше 2 месяцев */
INSERT INTO hits_intermediate
SELECT h.*
FROM hits h
INNER JOIN search_subscriptions s
	ON s.hit_id = h.id
WHERE h.time <= :keepHits;
SQL;
    $this->db->createCommand($sql, [':keepHits' => $keepHits])->execute();
    $this->log('Готово');

    $this->log('Переносим хиты с единоразовыми подписками старше ' . $keepHits . ' дней');
    $sql = <<<SQL
/* Переносим хиты с единоразовыми подписками старше 2 месяцев */
INSERT IGNORE INTO hits_intermediate
SELECT h.*
FROM hits h
INNER JOIN onetime_subscriptions s
	ON s.hit_id = h.id
WHERE h.time <= :keepHits;
SQL;
    $this->db->createCommand($sql, [':keepHits' => $keepHits])->execute();
    $this->log('Готово');

    $this->log('Create table hit_params_intermediate');
    $sql = <<<SQL
/* HIT_PARAMS */
CREATE TABLE `hit_params_intermediate` (
	`hit_id` INT(10) UNSIGNED NOT NULL,
	`ip` BIGINT(12) NULL DEFAULT NULL,
	`referer` VARCHAR(512) NULL DEFAULT NULL COLLATE "utf8_unicode_ci",
	`user_agent` VARCHAR(512) NULL DEFAULT NULL COLLATE "utf8_unicode_ci",
	`label1` VARCHAR(512) NULL DEFAULT NULL COLLATE "utf8_unicode_ci",
	`label2` VARCHAR(512) NULL DEFAULT NULL COLLATE "utf8_unicode_ci",
	`subid1` VARCHAR(512) NULL DEFAULT NULL COLLATE "utf8_unicode_ci",
	`subid2` VARCHAR(512) NULL DEFAULT NULL COLLATE "utf8_unicode_ci",
	`get_params` VARCHAR(2048) NULL DEFAULT NULL COLLATE "utf8_unicode_ci",
	PRIMARY KEY (`hit_id`)
)
COLLATE="utf8_unicode_ci"
ENGINE=InnoDB;
SQL;
    $this->db->createCommand($sql)->execute();
    $this->log('Готово');

    $this->log('Переносим данные в hit_params');
    $sql = <<<SQL
/**
 * Переносим данные в hit_params, кроме get_params
 */
INSERT INTO hit_params_intermediate
SELECT hp.hit_id,
  hp.ip,
  hp.referer,
  hp.user_agent,
  hp.label1,
  hp.label2, 
  hp.subid1, 
  hp.subid2, 
  null get_params
FROM hit_params hp
INNER JOIN hits_intermediate h
	ON hp.hit_id = h.id;
	
SQL;
    $this->db->createCommand($sql)->execute();
    $this->log('Готово');

    $this->log('Переносим get_params в hit_params');
    $sql = <<<SQL
/**
 * Переносим get_params за последние 2 месяца
 */
INSERT INTO hit_params_intermediate
SELECT hp.* FROM hit_params hp
INNER JOIN hits_intermediate h
	ON hp.hit_id = h.id
WHERE h.time > :keepHits
ON DUPLICATE KEY UPDATE get_params = VALUES(get_params);
SQL;
    $this->db->createCommand($sql, [':keepHits' => $keepHits])->execute();
    $this->log('Готово');

    $this->log('ДОЗАПИСЫВАЕМ НАКОПИВШИЕСЯ ДАННЫЕ ПОСЛЕ ОБРАБОТКИ hit_params ПЕРЕД ПЕРЕИМЕНОВАНИЕМ (maxHitID = '.$maxHitId.')');
    $sql = <<<SQL
/* ДОЗАПИСЫВАЕМ НАКОПИВШИЕСЯ ДАННЫЕ ПОСЛЕ ОБРАБОТКИ hit_params ПЕРЕД ПЕРЕИМЕНОВАНИЕМ */
INSERT IGNORE INTO hits_intermediate
SELECT * FROM hits
WHERE id > :maxHitId;
SQL;
    $this->db->createCommand($sql, [':maxHitId' => $maxHitId])->execute();
    $this->log('Перенесены данные из hits');

    $sql = <<<SQL
INSERT IGNORE INTO hit_params_intermediate
SELECT * FROM hit_params
WHERE hit_id > :maxHitId;
SQL;
    $this->db->createCommand($sql, [':maxHitId' => $maxHitId])->execute();
    $this->log('Перенесены данные из hit params');

    $dbLog = Yii::getLogger()->getProfiling(['yii\db*']);
    foreach ($dbLog as $query) {
      $this->log($query['info'] . "\n" . round($query['duration'], 5) . " sec");
    }

    $this->log('Конец скрипта');
  }

  /**
   * @param int $days
   * @return $this
   */
  public function setDaysKeep($days)
  {
    $this->_daysKeep = $days;
    return $this;
  }

}