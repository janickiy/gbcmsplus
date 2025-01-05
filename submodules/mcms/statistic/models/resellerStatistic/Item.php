<?php

namespace mcms\statistic\models\resellerStatistic;

use rgk\utils\components\CurrenciesValues;
use yii\base\Model;

/**
 * Модель представляет собой одну строку в гриде статистики (не важно как сгруппированной).
 * Поле группировки $group содержит инфу по значению и типу группировки статы.
 *
 * TRICKY При создании новых свойств нужно добавлять их в @see \mcms\statistic\models\resellerStatistic\Item::init,
 * иначе добро пожаловать в мир магических багов
 *
 * @property CurrenciesValues $resProfitCpa
 * @property CurrenciesValues $paid
 * @property CurrenciesValues $paidCount
 * @property CurrenciesValues $await
 * @property CurrenciesValues $awaitCount
 * @property CurrenciesValues $debt
 */
class Item extends Model
{
  /**
   * Ключ, по которому сгруппирована статистика.
   * @var Group|null
   */
  public $group;

  /**
   * Профит (оборот) реселлера
   * @var CurrenciesValues
   */
  public $resProfit;
  /**
   * Профит ревшар реселлера
   * @var CurrenciesValues
   */
  public $resProfitRevshare;
  /**
   * Профит за выкуп реселлера
   * @var CurrenciesValues
   */
  public $resProfitCpaSold;
  /**
   * Профит отклоненных CPA реселлера
   * @var CurrenciesValues
   */
  public $resProfitCpaRejected;
  /**
   * Профит onetime реселлера
   * @var CurrenciesValues
   */
  public $resProfitOnetime;
  /**
   * Сумма выплаченных реселлерских выплат
   * @var CurrenciesValues
   */
  public $resPaid;
  /**
   * Сумма выплаченных партнерских выплат
   * @var CurrenciesValues
   */
  public $partPaid;
  /**
   * Кол-во выплаченных реселлерских выплат
   * @var CurrenciesValues
   */
  public $resPaidCount;
  /**
   * Кол-во выплаченных партнерских выплат
   * @var CurrenciesValues
   */
  public $partPaidCount;
  /**
   * Сумма ещё невыплаченных реселлерских выплат
   * @var CurrenciesValues
   */
  public $resAwait;
  /**
   * Сумма ещё невыплаченных партнерских выплат
   * @var CurrenciesValues
   */
  public $partAwait;
  /**
   * Кол-во ещё невыплаченных реселлерских выплат
   * @var CurrenciesValues
   */
  public $resAwaitCount;
  /**
   * Кол-во ещё невыплаченных партнерских выплат
   * @var CurrenciesValues
   */
  public $partAwaitCount;
  /**
   * Сумма средств, которая расхолдилась в данной строке
   * (т.е. она могла захолдиться ранее, но расхолдилась именно сейчас)
   * @var CurrenciesValues
   */
  public $unholded;
  /**
   * Сумма средств, которая захолдилась в данной строке, но не успела расхолдиться в этом диапазоне
   * @var CurrenciesValues
   */
  public $holded;
  /**
   * Сумма штрафов
   * @var CurrenciesValues
   */
  public $penalties;
  /**
   * Сумма компенсаций
   * @var CurrenciesValues
   */
  public $compensations;
  /**
   * Кол-во штрафов
   * @var CurrenciesValues
   */
  public $penaltiesCount;
  /**
   * Кол-во компенсаций
   * @var CurrenciesValues
   */
  public $compensationsCount;

  /**
   * Сумма уменьшения баланса при конвертации
   * @var CurrenciesValues
   */
  public $convertDecreases;
  /**
   * Кол-во уменьшений баланса при конвертации
   * @var CurrenciesValues
   */
  public $convertDecreasesCount;
  /**
   * Сумма увеличения баланса при конвертации
   * @var CurrenciesValues
   */
  public $convertIncreases;
  /**
   * Кол-во увеличений баланса при конвертации
   * @var CurrenciesValues
   */
  public $convertIncreasesCount;
  /**
   * Сумма взятых кредитов
   * @var CurrenciesValues
   */
  public $credits;
  /**
   * Кол-во взятых кредитов
   * @var CurrenciesValues
   */
  public $creditsCount;
  /**
   * Сумма списаний за кредиты (выплаты с баланса и регулярные списания процентов)
   * @var CurrenciesValues
   */
  public $creditCharges;
  /**
   * Ссылка на серч-модель.
   * Нужно потом доставать инфу, например для того чтобы корректно показать граничные даты в столбце Неделя|Месяц.
   * @var  ItemSearch
   */
  public $searchModel;

  public function init()
  {
    parent::init();
    $currenciesValuesAttributes = [
      'resProfit', 'resProfitRevshare', 'resProfitCpaSold', 'resProfitCpaRejected',
      'resProfitOnetime', 'resPaid', 'partPaid', 'resPaidCount', 'partPaidCount',
      'resAwait', 'partAwait', 'resAwaitCount', 'partAwaitCount', 'unholded', 'holded',
      'penalties', 'compensations', 'penaltiesCount', 'compensationsCount',
      'credits', 'creditsCount', 'creditCharges',
      'convertIncreases', 'convertIncreasesCount', 'convertDecreases', 'convertDecreasesCount'
    ];

    // При ините все поля должны быть объектами CurrenciesValues
    foreach ($currenciesValuesAttributes as $attribute) {
      if (!isset($this->{$attribute})) $this->{$attribute} = CurrenciesValues::createEmpty();
    }
  }

  /**
   * Профит CPA реселлера
   * @return CurrenciesValues
   */
  public function getResProfitCpa()
  {
    $values = clone $this->resProfitCpaSold;
    return $values->plusValues($this->resProfitCpaRejected);
  }

  /**
   * Достаём сумму совершенных выплат (суммируем сумму выплат ресу и его партнерам)
   * @return CurrenciesValues
   */
  public function getPaid()
  {
    $resValues = clone $this->resPaid;
    return $resValues->plusValues($this->partPaid);
  }

  /**
   * Достаём кол-во совершенных выплат (суммируем сумму выплат ресу и его партнерам)
   * @return CurrenciesValues
   */
  public function getPaidCount()
  {
    $resValues = clone $this->resPaidCount;
    return $resValues->plusValues($this->partPaidCount);
  }

  /**
   * Достаём сумму выплат в ожидании (суммируем сумму выплат ресу и его партнерам)
   * @return CurrenciesValues
   */
  public function getAwait()
  {
    $resValues = clone $this->resAwait;
    return $resValues->plusValues($this->partAwait);
  }

  /**
   * Достаём кол-во выплат в ожидании (суммируем сумму выплат ресу и его партнерам)
   * @return CurrenciesValues
   */
  public function getAwaitCount()
  {
    $resValues = clone $this->resAwaitCount;
    return $resValues->plusValues($this->partAwaitCount);
  }

  /**
   * Достаём доступную к снятию сумму реса.
   * Для этого из того что расхолдилось в этой строке вычитаем то что уже было выплачено.
   *
   * @return CurrenciesValues
   */
  public function getDebt()
  {
    $unholded = clone $this->unholded;
    return $unholded
      ->minusValues($this->await)
      ->minusValues($this->penalties)
      ->minusValues($this->creditCharges)
      ->plusValues($this->credits)
      ->plusValues($this->compensations)
      ->minusValues($this->convertDecreases)
      ->plusValues($this->convertIncreases);
  }
}
