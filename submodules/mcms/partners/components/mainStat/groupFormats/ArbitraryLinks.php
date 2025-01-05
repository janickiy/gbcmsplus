<?php

namespace mcms\partners\components\mainStat\groupFormats;

use yii\helpers\Html;

/**
 * форматтер для ссылок арбитражника
 */
class ArbitraryLinks extends WebmasterSources
{
  /**
   * делаем ссылку
   * @param $title
   * @return string
   */
  protected function makeLink($title)
  {
    return Html::a($title, ['links/add', 'id' => $this->value, '#' => 'step_1'], ['data-pjax' => 0]);
  }
}
