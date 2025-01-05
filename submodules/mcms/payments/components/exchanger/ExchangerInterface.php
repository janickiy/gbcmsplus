<?php

namespace mcms\payments\components\exchanger;

interface ExchangerInterface
{
  /**
   * получение текущего курса валют
   * @return CurrencyCourses
   */
  public function getExchangerCourses();
}