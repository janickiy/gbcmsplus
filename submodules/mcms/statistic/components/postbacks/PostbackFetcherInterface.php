<?php

namespace mcms\statistic\components\postbacks;


/**
 * Interface PostbackFetcherInterface
 * @package mcms\statistic\components\postbacks
 */
interface PostbackFetcherInterface
{
  /**
   * Возвращает количество записей для отправки постбеков
   * @return int
   */
  public function getCount();

  /**
   * Возвращает массив/итератор с записями для отправки постбеков
   * @param int $size
   * @return array|iterable
   */
  public function each($size = 100);

  /**
   * Возвращает массив/итератор с пачками записей для отправки в очередь
   * @return array|iterable
   */
  public function batch($size = 100);
}