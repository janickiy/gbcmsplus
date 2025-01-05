<?php

namespace mcms\payments\components\resellerStatistic;

use mcms\statistic\models\resellerStatistic\PaymentStatItemInterface;
use rgk\utils\components\CurrenciesValues;
use yii\base\Object;

/**
 * Модель для статистики реселлера, грубо говоря представляет собой одну строчку в таблице статистики, но одержит только данные по выплатам.
 *
 * TRICKY При создании новых свойств нужно добавлять их в @see \mcms\payments\components\resellerStatistic\PaymentStatItem,
 * иначе добро пожаловать в мир магических багов
 */
class PaymentStatItem extends Object implements PaymentStatItemInterface
{
  /**
   * @var string ключ группировки
   */
  public $groupValue;
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

  public function init()
  {
    parent::init();
    $currenciesValuesAttributes = [
      'resPaid', 'partPaid', 'resPaidCount', 'partPaidCount',
      'resAwait', 'partAwait', 'resAwaitCount', 'partAwaitCount',
      'penalties', 'penaltiesCount', 'compensations', 'compensationsCount',
      'credits', 'creditsCount', 'creditCharges',
      'convertIncreases', 'convertIncreasesCount', 'convertDecreases', 'convertDecreasesCount'
    ];

    // При ините все поля должны быть объектами CurrenciesValues
    foreach ($currenciesValuesAttributes as $attribute) {
      if (!isset($this->{$attribute})) $this->{$attribute} = CurrenciesValues::createEmpty();
    }
  }

  /**
   * @inheritdoc
   */
  public function getGroupValue()
  {
    return $this->groupValue;
  }

  /**
   * @inheritdoc
   */
  public function getResPaid()
  {
    return $this->resPaid;
  }

  /**
   * @inheritdoc
   */
  public function getResPaidCount()
  {
    return $this->resPaidCount;
  }

  /**
   * @inheritdoc
   */
  public function getPartPaid()
  {
    return $this->partPaid;
  }

  /**
   * @inheritdoc
   */
  public function getPartPaidCount()
  {
    return $this->partPaidCount;
  }

  /**
   * @inheritdoc
   */
  public function getResAwait()
  {
    return $this->resAwait;
  }

  /**
   * @inheritdoc
   */
  public function getResAwaitCount()
  {
    return $this->resAwaitCount;
  }

  /**
   * @inheritdoc
   */
  public function getPartAwait()
  {
    return $this->partAwait;
  }

  /**
   * @inheritdoc
   */
  public function getPartAwaitCount()
  {
    return $this->partAwaitCount;
  }

  /**
   * @inheritdoc
   */
  public function getPenalties()
  {
    return $this->penalties;
  }

  /**
   * @inheritdoc
   */
  public function getPenaltiesCount()
  {
    return $this->penaltiesCount;
  }

  /**
   * @inheritdoc
   */
  public function getConvertIncreases()
  {
    return $this->convertIncreases;
  }

  /**
   * @inheritdoc
   */
  public function getConvertIncreasesCount()
  {
    return $this->convertIncreasesCount;
  }

  /**
   * @inheritdoc
   */
  public function getConvertDecreases()
  {
    return $this->convertDecreases;
  }

  /**
   * @inheritdoc
   */
  public function getConvertDecreasesCount()
  {
    return $this->convertDecreasesCount;
  }

  /**
   * @inheritdoc
   */
  public function getCompensations()
  {
    return $this->compensations;
  }

  /**
   * @inheritdoc
   */
  public function getCompensationsCount()
  {
    return $this->compensationsCount;
  }

  /**
   * @inheritdoc
   */
  public function getCredits()
  {
    return $this->credits;
  }

  /**
   * @inheritdoc
   */
  public function getCreditsCount()
  {
    return $this->creditsCount;
  }

  /**
   * @inheritdoc
   */
  public function getCreditCharges()
  {
    return $this->creditCharges;
  }
}
