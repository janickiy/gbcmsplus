<?php

namespace mcms\partners\components\mainStat\groupFormats;

/**
 * Форматтер для платформ
 */
class Platforms extends \mcms\statistic\components\mainStat\mysql\groupFormats\Platforms
{
  /** @var string шаблон текста */
  public $template = '{name}';

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
