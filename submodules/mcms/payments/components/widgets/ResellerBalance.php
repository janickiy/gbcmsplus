<?php

namespace mcms\payments\components\widgets;


use mcms\payments\assets\ResellerBalanceAssets;
use mcms\payments\components\UserBalance;
use mcms\payments\models\UserPayment;
use yii\base\Widget;

/**
 * Class ResellerBalance
 * @package mcms\payments\components\widgets
 */
class ResellerBalance extends Widget
{

  /**
   * @return string
   */
  public function run()
  {
    ResellerBalanceAssets::register($this->view);
    $resellerBalance = [
      'rub' => $this->getResellerBalanceByCurrency('rub'),
      'usd' => $this->getResellerBalanceByCurrency('usd'),
      'eur' => $this->getResellerBalanceByCurrency('eur')
    ];

    return $this->render('reseller_balance', [
      'resellerBalance' => $resellerBalance,
      'awaitingSums' => UserPayment::getResellerAwaitingPaymentSums()
    ]);
  }

  /**
   * @param $currency
   * @return float
   */
  protected function getResellerBalanceByCurrency($currency)
  {
    return (new UserBalance(['userId' => UserPayment::getResellerId(), 'currency' => $currency]))->getResellerBalance();
  }
}