<?php

namespace mcms\statistic\models\resellerStatistic;

/**
 * Интерфейс для получения сумм выплат реселлера или партнерам через процессинг RGK.
 * Правильным путём будет реализовать этот интерфейс в модуле выплат и там же сделать определение
 * этой реализации в контейнере. А в модуле статы использовать уже реализацию из контейнера.
 *
 * Interface PaymentsStatFetchInterface
 * @package mcms\statistic\models\resellerStatistic
 */
interface PaymentsStatFetchInterface
{
  /**
   * Установить тип группировки статы по дням
   * @return $this
   */
  public function setGroupTypeDay();
  /**
   * Установить тип группировки статы по неделям
   * @return $this
   */
  public function setGroupTypeWeek();
  /**
   * Установить тип группировки статы по месяцам
   * @return $this
   */
  public function setGroupTypeMonth();
  /**
   * Установить фильтр по дате
   * @param $value
   * @return $this
   */
  public function setDateFrom($value);
  /**
   * Установить фильтр по дате
   * @param $value
   * @return $this
   */
  public function setDateTo($value);
  /**
   * Передать фейковый ключ для группировки.
   * @param $value
   * @return $this
   */
  public function setFakeGroupType($value);
  /**
   * Получить статистику в виде моделей
   * @return PaymentStatItemInterface[]
   */
  public function getModels();
}
