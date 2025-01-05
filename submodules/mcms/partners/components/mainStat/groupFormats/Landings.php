<?php

namespace mcms\partners\components\mainStat\groupFormats;

/**
 * Форматтер для лендов
 */
class Landings extends \mcms\statistic\components\mainStat\mysql\groupFormats\Landings
{
  /** @var string шаблон текста */
  public $template = '{id}. {name}';

  /**
   * @param $title
   * @return string
   */
  protected function makeLink($title)
  {
    return $title;
  }
}
