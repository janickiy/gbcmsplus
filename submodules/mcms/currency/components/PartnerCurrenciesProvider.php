<?php
namespace mcms\currency\components;

use mcms\currency\models\Currency;
use rgk\exchange\components\BaseCurrenciesProvider;
use rgk\exchange\components\Currencies;
use rgk\exchange\models\Currency as ExchangeCurrency;

/**
 * Компонент получения патнерских курсов валют
 */
class PartnerCurrenciesProvider extends BaseCurrenciesProvider
{
  /**
   * @var Currencies
   */
  protected $_courses = [];

  /**
   * @var array
   */
  protected $_coursesAsArray = [];

  protected static $instance;

  /**
   * @return PartnerCurrenciesProvider
   */
  public static function getInstance()
  {
    if (!self::$instance) {
      // была утечка, переделал на сигнлтон
      self::$instance = new self();
    }

    return self::$instance;
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
   * Если задан custom курс и он выгоден, выводим его. Иначе - курс с наложеным процентом
   * Рассчет выгодности курса смотреть @see \mcms\currency\models\Currency::isCustomCourseProfitable()
   * @param Currency $currency
   * @return ExchangeCurrency
   */
  protected function getCoursesWithPercent($currency)
  {
    $toRub =  $currency->custom_to_rub && $currency->isCustomCourseProfitable('rub')
      ? $currency->custom_to_rub
      : $currency->to_rub * (100 - $currency->partner_percent_rub) / 100;

    $toUsd =  $currency->custom_to_usd && $currency->isCustomCourseProfitable('usd')
      ? $currency->custom_to_usd
      : $currency->to_usd * (100 - $currency->partner_percent_usd) / 100;

    $toEur =  $currency->custom_to_eur && $currency->isCustomCourseProfitable('eur')
      ? $currency->custom_to_eur
      : $currency->to_eur * (100 - $currency->partner_percent_eur) / 100;

    return new ExchangeCurrency($currency->id, $currency->code, $toRub, $toUsd, $toEur);
  }

  /**
   * Получить курсы основных валют в виде массива
   * Используется в ПП
   * @return array
   */
  public function getCoursesAsArray()
  {
    if (!$this->_coursesAsArray) {
      $rubCourses = $this->getCurrencies()->getCurrency('rub');
      $usdCourses = $this->getCurrencies()->getCurrency('usd');
      $eurCourses = $this->getCurrencies()->getCurrency('eur');

      $this->_coursesAsArray = [
        'usd_rub' => $usdCourses->getToRub(),
        'usd_eur' => $usdCourses->getToEur(),
        'eur_rub' => $eurCourses->getToRub(),
        'eur_usd' => $eurCourses->getToUsd(),
        'rub_usd' => $rubCourses->getToUsd(),
        'rub_eur' => $rubCourses->getToEur(),
      ];
    }
    return $this->_coursesAsArray;
  }

}