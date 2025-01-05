<?php

namespace mcms\statistic\models\resellerStatistic;

use rgk\utils\components\CurrenciesValues;

/**
 * Интерфейс для одной строки статистики для сумм выплат реселлера или партнерам через процессинг RGK.
 * Правильным путём будет реализовать этот интерфейс в модуле выплат и там же сделать определение
 * этой реализации в контейнере. А в модуле статыы использовать уже реализацию из контейнера.
 *
 * Interface PaymentStatItemInterface
 * @package mcms\statistic\models\resellerStatistic
 */
interface PaymentStatItemInterface
{
  /**
   * ключ группировки
   * @return string
   */
  public function getGroupValue();
  /**
   * Сумма выплаченных реселлерских выплат
   * @return CurrenciesValues
   */
  public function getResPaid();
  /**
   * Кол-во выплаченных реселлерских выплат
   * @return CurrenciesValues
   */
  public function getResPaidCount();
  /**
   * Сумма выплаченных партнерских выплат
   * @return CurrenciesValues
   */
  public function getPartPaid();
  /**
   * Кол-во выплаченных партнерских выплат
   * @return CurrenciesValues
   */
  public function getPartPaidCount();
  /**
   * Сумма ещё невыплаченных реселлерских выплат
   * @return CurrenciesValues
   */
  public function getResAwait();
  /**
   * Кол-во ещё невыплаченных реселлерских выплат
   * @return CurrenciesValues
   */
  public function getResAwaitCount();
  /**
   * Сумма ещё невыплаченных партнерских выплат
   * @return CurrenciesValues
   */
  public function getPartAwait();
  /**
   * Кол-во ещё невыплаченных партнерских выплат
   * @return CurrenciesValues
   */
  public function getPartAwaitCount();
  /**
   * Сумма штрафов
   * @return CurrenciesValues
   */
  public function getPenalties();
  /**
   * Кол-во штрафов
   * @return CurrenciesValues
   */
  public function getPenaltiesCount();

  /**
   * Сумма увеличения баланса при конвертации
   * @return CurrenciesValues
   */
  public function getConvertIncreases();
  /**
   * Кол-во увеличений баланса при конвертации
   * @return CurrenciesValues
   */
  public function getConvertIncreasesCount();
  /**
   * Сумма уменьшения баланса при конвертации
   * @return CurrenciesValues
   */
  public function getConvertDecreases();
  /**
   * Кол-во уменьшений баланса при конвертации
   * @return CurrenciesValues
   */
  public function getConvertDecreasesCount();
  /**
   * Сумма компенсаций
   * @return CurrenciesValues
   */
  public function getCompensations();
  /**
   * Кол-во компенсаций
   * @return CurrenciesValues
   */
  public function getCompensationsCount();
  /**
   * Сумма взятых кредитов
   * @return CurrenciesValues
   */
  public function getCredits();
  /**
   * Кол-во взятых кредитов
   * @return CurrenciesValues
   */
  public function getCreditsCount();
  /**
   * Сумма списаний за кредиты (выплаты с баланса и регулярные списания процентов)
   * @return CurrenciesValues
   */
  public function getCreditCharges();
}
