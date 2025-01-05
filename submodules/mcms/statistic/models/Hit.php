<?php
namespace mcms\statistic\models;

/**
 * Класс модели хита
 */
class Hit
{
  /** @see Hit::$subType */
  const SUB_TYPE_ONETIME = 'onetime';
  /** @see Hit::$subType */
  const SUB_TYPE_SUB = 'sub';

  /**
   * @var int Хит айди
   */
  public $id;
  /**
   * @var int Время хита
   */
  public $time;
  /**
   * @var string Тип подписки onetime|sub
   */
  public $subType;
  /**
   * @var float Цена за ребилл
   */
  public $rebillPriceRub;
  /**
   * @var float Цена за ребилл
   */
  public $rebillPriceUsd;
  /**
   * @var float Цена за ребилл
   */
  public $rebillPriceEur;
  /**
   * @var float Цена за ребилл в дефолтной валюте
   */
  public $defaultCurrencyRebillPrice;
  /**
   * @var int Валюта
   */
  public $defaultCurrencyId;
  /**
   * @var Sub Подписка по этому хиту
   */
  public $sub;

  /**
   * Получение кода валюты (rub, usd, eur) по полю @see Hit::$defaultCurrencyId
   * @return string
   */
  public function getDefaultCurrencyCode()
  {
    if (!$this->defaultCurrencyId) return null;
    return [1 => 'rub', 2 => 'usd', 3 => 'eur'][$this->defaultCurrencyId];
  }
}