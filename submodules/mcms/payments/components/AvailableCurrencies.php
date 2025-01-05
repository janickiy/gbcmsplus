<?php

namespace mcms\payments\components;

use Yii;
use mcms\payments\components\api\UserBalanceConvertHandler;

/**
 * Доступные валюты партнеру которые включают в себя включенные в настройках и валюта текущего и старого баланса, если
 * он есть
 * Class AvailableCurrencies
 */
class AvailableCurrencies
{
  /* @var UserBalanceConvertHandler $userBalanceConvertHandler */
  private $userBalanceConvertHandler;
  /* @var array */
  private $mainCurrencies;

  /**
   * @param $userId
   * @throws \mcms\common\exceptions\api\ApiResultInvalidException
   * @throws \mcms\common\exceptions\api\ClassNameNotDefinedException
   */
  public function __construct($userId)
  {
    /* @var \mcms\payments\Module $paymentsModule */
    $paymentsModule = Yii::$app->getModule('payments');
    /* @var \mcms\promo\Module $promoModule */
    $promoModule = Yii::$app->getModule('promo');

    $this->userBalanceConvertHandler = $paymentsModule->api('userBalanceConvertHandler', [
      'userId' => $userId,
    ]);
    $this->mainCurrencies = $promoModule->api('mainCurrencies', ['availablesOnly' => true])
      ->setMapParams(['code', 'name'])->getMap();
  }

  /**
   * Возвращает доступные валюты
   * @return string[]
   */
  public function getCurrencies()
  {
    $availableCurrencies = array_keys($this->mainCurrencies);

    if ($this->userBalanceConvertHandler->getHasOldBalance()) {
      $availableCurrencies[] = $this->userBalanceConvertHandler->getOldUserBalance()->currency;
    }
    $availableCurrencies[] = $this->userBalanceConvertHandler->getCurrentUserBalance()->currency;

    return array_unique($availableCurrencies);
  }
}
