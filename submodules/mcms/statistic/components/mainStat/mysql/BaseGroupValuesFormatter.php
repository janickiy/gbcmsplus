<?php

namespace mcms\statistic\components\mainStat\mysql;

use mcms\statistic\components\mainStat\FormModel;

/**
 * Форматтер для группировок. Например чтоб дату отобразить не 2018-03-09, а 09.03.2018
 * Либо показать юзера не просто id, а в формате '#id. Name' и со ссылкой на просмотр юзера
 */
abstract class BaseGroupValuesFormatter
{

  protected $value;
  protected $formModel;

  /**
   * BaseGroupValuesFormatter constructor.
   * @param $value
   * @param FormModel $formModel
   */
  public function __construct($value, FormModel $formModel)
  {
    $this->value = $value;
    $this->formModel = $formModel;
  }

  /**
   * Получить отформатированное значение группировки
   * @return string
   */
  abstract public function getFormattedValue();
}