<?php

namespace mcms\partners\components\subidStat\query;

use mcms\partners\components\subidStat\FormModel;
use yii\db\Query;

/**
 * Базовый класс для запросов в БД
 */
abstract class BaseQuery extends Query
{
  /** @var FormModel */
  protected $formModel;

  /**
   * @return FormModel
   */
  public function getFormModel()
  {
    return $this->formModel;
  }

  /**
   * @param mixed $formModel
   */
  public function setFormModel($formModel)
  {
    $this->formModel = $formModel;
  }

  /**
   */
  abstract public function makePrepare();
}
