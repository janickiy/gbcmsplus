<?php

namespace mcms\statistic\components;


/**
 * Interface Formattable для управления форматированием в гриде из фильтров
 * @package mcms\statistic\components
 */
interface Formattable
{
  /**
   * Параметры для форматера
   * @return array
   */
  public function getFormatterParams();
}