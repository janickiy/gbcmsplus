<?php

namespace mcms\partners\components\mainStat\groupFormats;

/**
 * Форматтер для операторов
 */
class Operators extends \mcms\statistic\components\mainStat\mysql\groupFormats\Operators
{
  /** @var string шаблон текста */
  public $template = '{name}';
}
