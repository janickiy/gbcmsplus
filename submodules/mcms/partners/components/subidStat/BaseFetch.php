<?php

namespace mcms\partners\components\subidStat;

/**
 * Извлекаем стату
 */
abstract class BaseFetch
{
  private $formModel;

  /**
   * @param FormModel $formModel
   */
  public function __construct(FormModel $formModel)
  {
    $this->formModel = $formModel;
  }

  /**
   * @return FormModel
   */
  public function getFormModel()
  {
    return $this->formModel;
  }

  /**
   * @param array $config
   * @return DataProvider
   */
  abstract public function getDataProvider($config = []);
}
