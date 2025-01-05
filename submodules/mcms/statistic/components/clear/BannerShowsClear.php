<?php

namespace mcms\statistic\components\clear;
use mcms\common\output\ConsoleOutput;

/**
 *
 * Этапы:
 * Создаем временную таблицу. В неё переносим только свежие данные (за последние _daysKeep дней).
 * Потом переименовываем таблицы (активная становится с суффиксом _bak, временная становится активной).
 * Допереносим строки, которые успели набежать, пока переносили данные.
 *
 * Class BannerShowsClear
 * @package mcms\statistic\components\clear
 */
class BannerShowsClear extends AbstractCleaner
{

  /** @var  int */
  private $_daysKeep = 30;

  private $_maxShowId;
  private $_scriptTime;
  private $_keepFrom;

  public function init()
  {
    parent::init();
    $this->setLogger(new ConsoleOutput()); // логгер по-умолчанию в консоль
    $this->_maxShowId = $this->db->createCommand('SELECT MAX(id) FROM banner_shows')->queryScalar();

    $this->log('Max show id: ' . $this->_maxShowId);

    /* Создаем переменную со временем, чтобы не было пересечений id из-за разного времени, так как у нас 3 запроса */
    $this->_scriptTime = time();
    $this->_keepFrom = $this->_scriptTime - $this->_daysKeep * 24 * 60 * 60;

    $this->log("SCRIPT STARTED WITH NEXT DATA:");
    $this->log("\nscriptTime: $this->_scriptTime\nkeep from: $this->_keepFrom\nmaxShowId: $this->_maxShowId");
  }


  public function run()
  {
    if (!$this->_maxShowId) return;
    $this->copyFreshData();
    $this->renameTables();
    $this->copyRecentData();
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

  private function copyFreshData()
  {
    $this->log('createTable banner_shows_intermediate');

    $sql = <<<SQL
      CREATE TABLE `banner_shows_intermediate` (
       `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
       `time` INT(10) UNSIGNED NOT NULL,
       `date` DATE NOT NULL,
       `banner_id` MEDIUMINT(5) UNSIGNED NOT NULL,
       `hit_id` INT(10) UNSIGNED NULL DEFAULT NULL,
       `operator_id` MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT '0',
       `source_id` MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT '0',
       `platform_id` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
       PRIMARY KEY (`id`),
       UNIQUE INDEX `banner_shows_hit_id_{$this->_keepFrom}` (`hit_id`),
       INDEX `banner_shows_date_{$this->_keepFrom}` (`date`),
       INDEX `banner_shows_b_d_s_o_p_h_{$this->_keepFrom}` (`banner_id`, `date`, `source_id`, `operator_id`, `platform_id`, `hit_id`)
      )
      COLLATE='utf8_unicode_ci'
      ENGINE=InnoDB
      ;

SQL;

    $this->db->createCommand($sql)->execute();

    $this->log('Переносим все данные за последние N дней');
    /** @noinspection SqlResolve */
    $sql = <<<SQL
/* переносим все данные за последние _daysKeep дней */
INSERT INTO banner_shows_intermediate
SELECT bs.*
FROM banner_shows bs
WHERE bs.date >= :fromDate;
SQL;
    $this->db->createCommand($sql)->bindValue(':fromDate', date('Y-m-d', $this->_keepFrom))->execute();
    $this->log('Готово');
  }

  private function renameTables()
  {
    $this->log('Увеличиваем автоинкремент в таблице banner_shows_intermediate на 5000');
    $sql = <<<SQL
/* ПЕРЕИМЕНОВАНИЕ ТАБЛИЦ */
/* Увеличиваем автоинкремент, чтобы оставить промежуток для данных из таблицы banner_shows, которые нужно доперелить положим что 5000 будет достаточно за время переименования */
SET @new_index = (SELECT MAX(id) + 5000 FROM banner_shows);
SET @sql = CONCAT('ALTER TABLE banner_shows_intermediate AUTO_INCREMENT = ', @new_index);
PREPARE st FROM @sql;
EXECUTE st;
SQL;
    $this->db->createCommand($sql)->execute();
    $this->log('Готово');

    $this->log('Переименовываем таблицу banner_shows');
    /** @noinspection SqlResolve */
    $sql = <<<SQL
/* Переименовываем banner_shows */
ALTER TABLE banner_shows RENAME banner_shows_bak_$this->_scriptTime;
ALTER TABLE banner_shows_intermediate RENAME banner_shows;
SQL;
    $this->db->createCommand($sql)->execute();
    $this->log('Готово');
  }

  private function copyRecentData()
  {
    $this->log('Дозаписываем данные в таблицу banner_shows');
    /** @noinspection SqlInsertValues */
    /** @noinspection SqlResolve */
    $sql = <<<SQL
/* Дозаписываем данные */
INSERT IGNORE INTO banner_shows
SELECT * FROM banner_shows_bak_$this->_scriptTime
WHERE id > $this->_maxShowId;
SQL;
    $this->db->createCommand($sql)->execute();
    $this->log('Готово');
  }
}