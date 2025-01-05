<?php
namespace mcms\statistic\components\mainStat;

use mcms\statistic\components\mainStat\mysql\Row;

/**
 * Интерфейс для датапровайдера статистики
 */
interface DataProviderInterface {
  /**
   * @return Row
   */
  public function getFooterRow();

  /**
   * @param Row $row
   */
  public function setFooterRow($row);
}