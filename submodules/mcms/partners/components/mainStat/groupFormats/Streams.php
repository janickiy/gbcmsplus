<?php

namespace mcms\partners\components\mainStat\groupFormats;

/**
 * Форматтер для потоков
 */
class Streams extends \mcms\statistic\components\mainStat\mysql\groupFormats\Streams
{
  /** @var string шаблон текста */
  public $template = '{id}. {name}';

  /**
   * делаем ссылку
   * @param $title
   * @return string
   */
  protected function makeLink($title)
  {
    return $title;
  }
}
