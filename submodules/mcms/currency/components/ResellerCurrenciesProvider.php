<?php
namespace mcms\currency\components;

use mcms\currency\models\Currency;
use rgk\exchange\components\BaseCurrenciesProvider;
use rgk\exchange\components\Currencies;
use rgk\exchange\models\Currency as ExchangeCurrency;

/**
 * Компонент получения патнерских курсов валют
 */
class ResellerCurrenciesProvider extends BaseCurrenciesProvider
{
  /**
   * @var Currencies
   */
  protected $_courses = [];

  /**
   * @return self
   */
  public static function getInstance()
  {
    return new self();
  }

  /**
   * @return Currencies
   */
  public function getCurrencies()
  {

    if (!$this->_courses) {
      $this->_courses = new Currencies();
      $currencies = Currency::find()->all();
      foreach ($currencies as $currency) {
        /* @var $currency Currency*/
        $exchangeCurrency = $this->getCoursesWithPercent($currency);
        $this->_courses->addCurrency($exchangeCurrency);
      }
    }

    return $this->_courses;
  }

  /**
   * @param Currency $currency
   * @return ExchangeCurrency
   */
  protected function getCoursesWithPercent($currency)
  {
    $toRub =  $currency->to_rub * (100 - Currency::DEFAULT_RESELLER_PERCENT) / 100;
    $toUsd =  $currency->to_usd * (100 - Currency::DEFAULT_RESELLER_PERCENT) / 100;
    $toEur =  $currency->to_eur * (100 - Currency::DEFAULT_RESELLER_PERCENT) / 100;

    return new ExchangeCurrency($currency->id, $currency->code, $toRub, $toUsd, $toEur);
  }

}