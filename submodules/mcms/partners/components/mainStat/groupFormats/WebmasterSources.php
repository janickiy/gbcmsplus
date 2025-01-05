<?php

namespace mcms\partners\components\mainStat\groupFormats;

/**
 * Форматтер для источников вебмастеров
 */
class WebmasterSources extends \mcms\statistic\components\mainStat\mysql\groupFormats\WebmasterSources
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
    /** источники не делаем ссылкой */
    return $title;
  }
}
