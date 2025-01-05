<?php

namespace mcms\payments\components\widgets;


use mcms\payments\assets\ResellerBalanceAssets;
use mcms\payments\components\RemoteWalletBalances;
use mcms\payments\components\UserBalance;
use mcms\payments\models\UserPayment;
use mcms\payments\models\wallet\Wallet;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

/**
 * Class PartnersAwaitingsPaysums
 * @package mcms\payments\components\widgets
 */
class PartnersAwaitingsPaysums extends Widget
{

  /**
   * @return string
   */
  public function run()
  {
    $byWallets = [];

    foreach (UserPayment::getPartnerAwaitingPaysumsByWalletType() as $awaitingSum) {
      $byWallets[$this->getKey($awaitingSum['wallet_type'], $awaitingSum['currency'])] = [
        'walletType' => $awaitingSum['wallet_type'],
        'currency' => $awaitingSum['currency'],
        'awaitingSum' => $awaitingSum['sum'],
      ];
    }

    /** @var RemoteWalletBalances $service */
    $service = Yii::$container->get('mcms\payments\components\RemoteWalletBalances');

    $balances = [];
    foreach ($service->getReadyToAutopayWallets() as $wallet) {
      $balances[$this->getKey($wallet['walletType'], $wallet['currency'])] = [
        'isBalanceAvailable' => true,
        'walletType' => $wallet['walletType'],
        'currency' => $wallet['currency'],
        'apiId' => $wallet['apiId'],
        'balanceFromCache' => $wallet['balanceFromCache'],
      ];
    }

    $byWallets = ArrayHelper::merge($byWallets, $balances);

    /** @var Wallet[] $walletTypes */
    $walletTypes = Wallet::find()
      ->where(['id' => ArrayHelper::getColumn($byWallets, 'walletType')])
      ->indexBy('id')
      ->all();

    foreach ($byWallets as &$wallet) {
      $wallet['walletName'] = $walletTypes[$wallet['walletType']]->name;
    }
    return $this->render('partner_awaiting_paysums', [
      'totalsByCurrency' => UserPayment::getPayableSummaryGroupedByCurrency(),
      'byWallets' => $byWallets,
    ]);
  }

  /**
   * @param $walletType
   * @param $currency
   * @return string
   */
  protected function getKey($walletType, $currency)
  {
    return $walletType . '_' . $currency;
  }
}