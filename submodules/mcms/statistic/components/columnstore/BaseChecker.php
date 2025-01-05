<?php


namespace mcms\statistic\components\columnstore;

/**
 * Базовый экспортер
 */
abstract class BaseChecker
{
  /** @var string */
  public $dateFrom;
  /** @var string */
  public $dateTo;

  /**
   * количество записей в innoDb
   * @return int
   */
  abstract public function getInnoDbCount();
  /**
   * количество записей в columnstore
   * @return int
   */
  abstract public function getColumnStoreCount();
  /**
   * количество дублей записей в columnstore
   * @return int
   */
  abstract public function getColumnStoreDuplicatesCount();
}
