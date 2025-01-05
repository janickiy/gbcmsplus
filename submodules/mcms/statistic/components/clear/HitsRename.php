<?php

namespace mcms\statistic\components\clear;
use mcms\common\output\ConsoleOutput;
use Yii;

/**
 *
 *
 * Class HitsRename
 * @package mcms\statistic\components\clear
 */
class HitsRename extends AbstractCleaner
{
  private $_scriptTime;
  public $updateFrom;

  public function init()
  {
    parent::init();
    $this->setLogger(new ConsoleOutput()); // логгер по-умолчанию в консоль

    /* Создаем переменную со временем, чтобы не было пересечений id из-за разного времени, так как у нас 3 запроса */
    $this->_scriptTime = time();
  }


  public function run()
  {
    $this->log("SCRIPT STARTED WITH NEXT DATA: ");
    $this->log("
      scriptTime: ". date('H:i:s d.m.Y', $this->_scriptTime) . "
    ");

    /*
     * Дозаполняем таблицу intermediate (через INSERT ON DUPLICATE KEY UPDATE)
     * Увеличиваем автоинкремент на 5000 от текущей таблицы hits (для переименования должно хватить)
     * Переименовываем таблицы
     * Дозаполняем таблицу hits из hits_bak_234234343 (!!! через INSERT ON DUPLICATE KEY UPDATE)
     *
     */
    $intermediateMaxHitId =  $this->db->createCommand('SELECT MAX(id) FROM hits_intermediate')->queryScalar();
    $this->log('Intermediate table max hit id: ' . $intermediateMaxHitId);
    $scriptTime = $this->_scriptTime;

    // это значение автоинкремента нужно для того чтобы обновить данные, уже после переименования таблиц
    $beforeRenameHitId = $this->db->createCommand('SELECT MAX(id) FROM hits')->queryScalar();
    $this->log('Before rename hit id: ' . $beforeRenameHitId);

    $this->log('ДОЗАПИСЫВАЕМ ДАННЫЕ В ТАБЛИЦУ hits_intermediate');
    $sql = <<<SQL
/* ДОЗАПИСЫВАЕМ НАКОПИВШИЕСЯ ДАННЫЕ ПОСЛЕ СКРИПТА intermediate */
INSERT INTO hits_intermediate
SELECT * FROM hits h
WHERE h.time > :updateFrom
ON DUPLICATE KEY UPDATE
  source_id = h.source_id,
  is_cpa = h.is_cpa
SQL;
    $this->db->createCommand($sql, [':updateFrom' => $this->updateFrom])->execute();
    $this->log('Перенесены данные из hits');

    $sql = <<<SQL
/* ДОЗАПИСЫВАЕМ ДАННЫЕ ПОСЛЕ СКРИПТА intermediate */
INSERT IGNORE INTO hit_params_intermediate
SELECT * FROM hit_params
WHERE hit_id > :intermediateMaxHitId;
SQL;
    $this->db->createCommand($sql, [':intermediateMaxHitId' => $intermediateMaxHitId])->execute();
    $this->log('Перенесены данные из hit params');


    $this->log('Увеличиваем автоинкремент в таблице hits_intermediate на 5000');
    $sql = <<<SQL
/* ПЕРЕИМЕНОВАНИЕ ТАБЛИЦ */
/* Увеличиваем автоинкремент, чтобы оставить промежуток для данных из таблицы hits, которые нужно доперелить положим что 5000 будет достаточно за время переименования */
SET @new_index = (SELECT MAX(id) + 5000 FROM hits);
SET @sql = CONCAT('ALTER TABLE hits_intermediate AUTO_INCREMENT = ', @new_index);
PREPARE st FROM @sql;
EXECUTE st;
SQL;
    $this->db->createCommand($sql)->execute();
    $this->log('Готово');

    $this->log('Переименовываем таблицу hits');
    $sql = <<<SQL
/* Переименовываем hits */
ALTER TABLE hits RENAME hits_bak_$scriptTime;
ALTER TABLE hits_intermediate RENAME hits;
SQL;
    $this->db->createCommand($sql)->execute();
    $this->log('Готово');

    $this->log('Дозаписываем данные в таблицу hits');
    $sql = <<<SQL
/* Дозаписываем данные */
INSERT INTO hits
SELECT * FROM hits_bak_$scriptTime hb
WHERE hb.id > :beforeRenameHitId
ON DUPLICATE KEY UPDATE
  source_id = hb.source_id,
  is_cpa = hb.is_cpa
SQL;
    $this->db->createCommand($sql, [':beforeRenameHitId' => $beforeRenameHitId])->execute();
    $this->log('Готово');

    $this->log('Переименовываем таблицу hit_params');
    $sql = <<<SQL
/* Переименовываем hit_params */
ALTER TABLE hit_params RENAME hit_params_bak_$scriptTime;
ALTER TABLE hit_params_intermediate RENAME hit_params;
SQL;
    $this->db->createCommand($sql)->execute();
    $this->log('Готово');

    $this->log('Дозаписываем данные в таблицу hit_params');
    $sql = <<<SQL
/* Дозаписываем данные */
INSERT IGNORE INTO hit_params 
SELECT * FROM hit_params_bak_$scriptTime
WHERE hit_id > :beforeRenameHitId;
SQL;
    $this->db->createCommand($sql, [':beforeRenameHitId' => $beforeRenameHitId])->execute();
    $this->log('Готово');

    $dbLog = Yii::getLogger()->getProfiling(['yii\db*']);
    foreach ($dbLog as $query) {
      $this->log($query['info'] . "\n" . round($query['duration'], 5) . " sec");
    }
  }
}