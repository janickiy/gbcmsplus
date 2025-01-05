<?php

namespace mcms\payments\components\exchanger;

use mcms\payments\Module;
use Yii;
use rgk\exchange\components\Currencies as RgkCurrencyCourses;

/**
 * Получение курсов из API geoapi.rgktools.com
 */
class GeoApiExchanger extends ExchangerAbstract
{
  // Процент конвертации валют по умолчанию
  const EXCHANGE_PERCENT = 2;

  /**
   * @return CurrencyCourses
   */
  public function getExchangerCourses()
  {
    return $this->getCurrencyCources();
  }

  /**
   * Данные по курсам из API
   * @return RgkCurrencyCourses
   */
  private function getApiCurrencies()
  {
    return Yii::$app->exchange->getCurrencies();
  }

  /**
   * TODO: Если в дальнейшем откажемся от других курсов, можно будет убрать этот костыль и использовать только CurrencyCourses из вендора
   *
   * Возвращаем полученные данные в виде модели CurrencyCourses
   * @return CurrencyCourses
   */
  private function getCurrencyCources()
  {
    $eurObj = $this->getApiCurrencies()->getCurrency('eur');
    $usdObj = $this->getApiCurrencies()->getCurrency('usd');
    $rubObj = $this->getApiCurrencies()->getCurrency('rub');

    $currencyCourses = new CurrencyCourses();
    $currencyCourses->eur_rur_real = $eurObj->getToRub();
    $currencyCourses->eur_usd_real = $eurObj->getToUsd();
    $currencyCourses->usd_eur_real = $usdObj->getToEur();
    $currencyCourses->usd_rur_real = $usdObj->getToRub();
    $currencyCourses->rur_eur_real = $rubObj->getToEur();
    $currencyCourses->rur_usd_real = $rubObj->getToUsd();

    $percent = (100 - self::EXCHANGE_PERCENT) / 100;
    $currencyCourses->eur_rur = $currencyCourses->eur_rur_real * $percent;
    $currencyCourses->eur_usd = $currencyCourses->eur_usd_real * $percent;
    $currencyCourses->usd_eur = $currencyCourses->usd_eur_real * $percent;
    $currencyCourses->usd_rur = $currencyCourses->usd_rur_real * $percent;
    $currencyCourses->rur_eur = $currencyCourses->rur_eur_real * $percent;
    $currencyCourses->rur_usd = $currencyCourses->rur_usd_real * $percent;

    /* @var $paymentsModule Module*/
    $paymentsModule = Yii::$app->getModule('payments');
    $currencyCourses->eur_rur_partner =  $currencyCourses->eur_rur_real * (100 - $paymentsModule->getExchangePercentEurRur()) / 100;
    $currencyCourses->eur_usd_partner =  $currencyCourses->eur_usd_real * (100 - $paymentsModule->getExchangePercentEurUsd()) / 100;
    $currencyCourses->usd_eur_partner =  $currencyCourses->usd_eur_real * (100 - $paymentsModule->getExchangePercentUsdEur()) / 100;
    $currencyCourses->usd_rur_partner =  $currencyCourses->usd_rur_real * (100 - $paymentsModule->getExchangePercentUsdRur()) / 100;
    $currencyCourses->rur_eur_partner =  $currencyCourses->rur_eur_real * (100 - $paymentsModule->getExchangePercentRurEur()) / 100;
    $currencyCourses->rur_usd_partner =  $currencyCourses->rur_usd_real * (100 - $paymentsModule->getExchangePercentRurUsd()) / 100;

    return $currencyCourses;
  }

}