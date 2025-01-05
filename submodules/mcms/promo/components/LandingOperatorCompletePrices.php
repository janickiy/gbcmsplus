<?php

namespace mcms\promo\components;

use mcms\currency\components\PartnerCurrenciesProvider;
use mcms\promo\components\api\MainCurrencies;
use mcms\promo\models\LandingOperator;
use rgk\utils\components\CurrenciesValues;

/**
 * Получаем полные цены за ленд-оператор. Суммы конвертируются во все три валюты
 */
class LandingOperatorCompletePrices
{
  /** @var LandingOperator */
  private $model;

  /**
   * локальный кэш
   * такой массив: ['rebill' => CurrenciesValues, 'buyout' => CurrenciesValues]
   * @var CurrenciesValues[]
   */
  private $completeValues;

  /**
   * Конструктор private чтоб объект создавался через @see LandingOperatorCompletePrices::create()
   * LandingOperatorCompletePrices constructor.
   * @param LandingOperator $model
   */
  private function __construct(LandingOperator $model)
  {
    $this->model = $model;
  }

  /**
   * @param LandingOperator $model
   * @return LandingOperatorCompletePrices
   */
  public static function create(LandingOperator $model)
  {
    return new self($model);
  }

  /**
   * @param $currency
   * @return float
   */
  public function getRebillPrice($currency)
  {
    return $this->getCompleteValues()['rebill']->getValue($currency);
  }

  /**
   * @param $currency
   * @return float
   */
  public function getBuyoutPrice($currency)
  {
    return $this->getCompleteValues()['buyout']->getValue($currency);
  }

  /**
   * @return CurrenciesValues[]
   */
  private function getCompleteValues()
  {
    if ($this->completeValues !== null) {
      return $this->completeValues;
    }
    $partnerCurrenciesProvider = PartnerCurrenciesProvider::getInstance();

    $convertedRebillPrices = $partnerCurrenciesProvider
      ->getCurrencies()
      ->getCurrencyById($this->model->local_currency_id);

    $this->completeValues['rebill'] = CurrenciesValues::createByValues([
      'rub' => $this->model->local_currency_rebill_price
        ? $convertedRebillPrices->convertToRub($this->model->local_currency_rebill_price)
        : (float)$this->model->rebill_price_rub,
      'usd' => $this->model->local_currency_rebill_price
        ? $convertedRebillPrices->convertToUsd($this->model->local_currency_rebill_price)
        : (float)$this->model->rebill_price_usd,
      'eur' => $this->model->local_currency_rebill_price
        ? $convertedRebillPrices->convertToEur($this->model->local_currency_rebill_price)
        : (float)$this->model->rebill_price_eur
    ]);

    $originalBuyoutCurrency = $this->model->getBuyoutCurrency()['code'];

    //TRICKY если указан выкуп в локальной валюте то выкупаем по ней
    $mainCurrencies = (new MainCurrencies())->getResult();
    foreach ($mainCurrencies as $mainCurrency) {
      if ($this->model->local_currency_id === $mainCurrency['id']
        && (float)$this->model->{'buyout_price_' . $mainCurrency['code']} > 0) {
        $originalBuyoutCurrency = $mainCurrency['code'];
      }
    }

    $convertedBuyoutPrices = $partnerCurrenciesProvider
      ->getCurrencies()
      ->getCurrency($originalBuyoutCurrency);

    $this->completeValues['buyout'] = CurrenciesValues::createByValues([
      'rub' => $convertedBuyoutPrices->convertToRub($this->model->{'buyout_price_' . $originalBuyoutCurrency}),
      'usd' => $convertedBuyoutPrices->convertToUsd($this->model->{'buyout_price_' . $originalBuyoutCurrency}),
      'eur' => $convertedBuyoutPrices->convertToEur($this->model->{'buyout_price_' . $originalBuyoutCurrency}),
    ]);

    return $this->completeValues;
  }
}
