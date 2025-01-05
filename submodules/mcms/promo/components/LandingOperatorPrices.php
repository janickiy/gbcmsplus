<?php

namespace mcms\promo\components;

use mcms\promo\models\Provider;
use rgk\utils\components\CurrenciesValues;
use yii\helpers\ArrayHelper;
use mcms\promo\models\LandingOperator;
use mcms\promo\Module;
use Yii;

/**
 * Пользоваться этим классом только когда надо посчитать цену для конкретного юзера.
 * Для остальных вещей есть модель @see LandingOperator
 */
class LandingOperatorPrices
{
  /**
   * @var  LandingOperator
   */
  private $model;
  /**
   * Для кого считаем профит
   * @var  int
   */
  private $partnerId;
  /**
   * @var  Module
   */
  private $module;
  /**
   * локальный кэш
   * такой массив: ['rebill' => CurrenciesValues, 'buyout' => CurrenciesValues, 'fixCpa' => CurrenciesValues]
   * @var CurrenciesValues[]
   */
  private $allValues;

  /**
   * @var int
   */
  public $providerId;

  private $personalPercents;
  private static $userCurrency;
  private static $_isDisablePayout;

  /**
   * Конструктор private чтоб объект создавался через @see LandingOperatorPrices::create()
   * @param LandingOperator $model
   * @param int $partnerId Для кого считаем профит
   */
  function __construct(LandingOperator $model, $partnerId)
  {
    $this->model = $model;
    $this->partnerId = $partnerId;
    $this->module = Yii::$app->getModule('promo');
  }

  /**
   * @param LandingOperator $model
   * @param int $partnerId Для кого считаем профит
   * @return LandingOperatorPrices
   */
  public static function create(LandingOperator $model, $partnerId)
  {
    return new self($model, $partnerId);
  }

  /**
   * За сколько выкупится пдп (прайс)
   * @param $currency
   * @return float
   */
  public function getBuyoutPrice($currency = null)
  {
    if (!$currency) {
      $currency = self::getUserCurrency();
    }
    return $this->getAllValues()['buyout']->getValue($currency);
  }

  /**
   * Сколько за выкуп получит партнер (с учетом фикс. цпа)
   * @param $currency
   * @return float
   */
  public function getBuyoutProfit($currency = null)
  {
    if (!$currency) {
      $currency = self::getUserCurrency();
    }
    return $this->getBuyoutFixCPA($currency) ?: $this->getBuyoutPrice($currency);
  }

  /**
     * Получить cpa price
     * Если у партнера выключен выкуп и это не вантайм, возвращаем 0, чтобы не отображать партнеру опцию CPA
     * @param $currency
     * @return float
   */
  public function getCpaPrice($currency = null)
  {
    if (!$currency) {
      $currency = self::getUserCurrency();
    }

    if ($this->model->isOnetime) {
      return $this->getRebillPrice($currency);
    }

    if (self::$_isDisablePayout === null) {
      /** @var \mcms\promo\components\api\UserPromoSettings $userPromoSettings */
      $userPromoSettings = Yii::$app->getModule('promo')->api('userPromoSettings');
      /** @var bool Запрещены ли выкупы партнеру $isDisableBuyout */
      self::$_isDisablePayout = $userPromoSettings->getIsDisableBuyout(Yii::$app->getUser()->getId());
    }

    if (self::$_isDisablePayout) {
      return 0;
    }
    return $this->getBuyoutFixCPA($currency) ?: $this->getBuyoutPrice($currency);
  }

  /**
   * Получаем фикс.цпа партнера
   * @param $currency
   * @return float
   */
  public function getBuyoutFixCPA($currency = null)
  {
    if (!$currency) {
      $currency = self::getUserCurrency();
    }
    return $this->getAllValues()['fixCpa']->getValue($currency);
  }

  /**
   * Цена за ребилл.
   * TRICKY: Эта цена используется в Onetime и на неё влияет fixCpa партнера
   * @param $currency
   * @return float
   */
  public function getRebillPrice($currency = null)
  {
    if (!$currency) {
      $currency = self::getUserCurrency();
    }

    // для onetime берём fixCPA если задано. Иначе цену за ребилл
    if ($this->model->isOnetime) {
      return $this->getAllValues()['fixCpa']->getValue($currency) ?: $this->getAllValues()['rebill']->getValue($currency);
    }

    return $this->getAllValues()['rebill']->getValue($currency);
  }

  /**
   * @return CurrenciesValues[]
   */
  private function getAllValues()
  {
    if ($this->allValues !== null) {
      return $this->allValues;
    }

    $partnerPercents = $this->getPartnerPercents();

    $rebillMultiplier = $partnerPercents['rebill_percent'] / 100;
    $buyoutMultiplier = $partnerPercents['buyout_percent'] / 100;

    $this->allValues = [
      'rebill' => CurrenciesValues::createByValues([
        'rub' => $rebillMultiplier * $this->model->getCompletePrices()->getRebillPrice('rub'),
        'usd' => $rebillMultiplier * $this->model->getCompletePrices()->getRebillPrice('usd'),
        'eur' => $rebillMultiplier * $this->model->getCompletePrices()->getRebillPrice('eur'),
      ]),
      'buyout' => CurrenciesValues::createByValues([
        'rub' => $buyoutMultiplier * $this->model->getCompletePrices()->getBuyoutPrice('rub'),
        'usd' => $buyoutMultiplier * $this->model->getCompletePrices()->getBuyoutPrice('usd'),
        'eur' => $buyoutMultiplier * $this->model->getCompletePrices()->getBuyoutPrice('eur'),
      ]),
      'fixCpa' => CurrenciesValues::createByValues([
        'rub' => ArrayHelper::getValue($partnerPercents, 'cpa_profit_rub'),
        'usd' => ArrayHelper::getValue($partnerPercents, 'cpa_profit_usd'),
        'eur' => ArrayHelper::getValue($partnerPercents, 'cpa_profit_eur'),
      ]),
    ];

    return $this->allValues;
  }

  /**
   * todo сделать метод приватным, для этого порефакторить выкуп
   * @return array
   */
  public function getPartnerPercents()
  {
    if ($this->personalPercents) {
      return $this->personalPercents;
    }

    $this->personalPercents = $this->module->api('personalProfit', [
      'userId' => $this->partnerId,
      'operatorId' => $this->model->operator_id,
      'landingId' => $this->model->landing_id,
      'providerId' => $this->model->landing->provider_id,
    ])->getResult();

    return $this->personalPercents;
  }

  /**
   * @return string
   */
  private static function getUserCurrency()
  {
    if (self::$userCurrency === null) {
      /** @var \mcms\payments\Module $paymentsModule */
      $paymentsModule = Yii::$app->getModule('payments');
      self::$userCurrency = $paymentsModule
        ->api('getUserCurrency', ['userId' => Yii::$app->user->id])
        ->getResult();
    }

    return self::$userCurrency;
  }
}
