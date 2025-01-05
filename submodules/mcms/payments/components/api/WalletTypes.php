<?php

namespace mcms\payments\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\payments\models\wallet\AbstractWallet;
use mcms\payments\models\wallet\Wallet;

/**
 * Платежные системы
 */
class WalletTypes extends ApiResult
{
  /**
   * @var bool|null
   * Активность ПС @see Wallet::find()
   */
  private $activity = null;
  /** @var string|null */
  private $currency;
  /** @var array */
  private $wallets = [];

  /**
   * @inheritdoc
   */
  function init($params = [])
  {
    $this->currency = ArrayHelper::getValue($params, 'currency', null);
    $this->activity = ArrayHelper::getValue($params, 'activity', null);
  }

  /**
   * Платежные системы
   * @return Wallet[]
   */
  public function getWallets()
  {
    $wallets = Wallet::find($this->activity)->all();

    if ($this->currency) {
      $walletClasses = Wallet::getWalletsClass();
      /** @var Wallet $wallet */
      foreach ($wallets as $key => $wallet) {
        /** @var AbstractWallet $walletClass */
        $walletClass = ArrayHelper::getValue($walletClasses, $wallet->id);
        if (!$walletClass || !in_array($this->currency, $walletClass::$currency)) unset($wallets[$key]);
      }
    }

    return $wallets;
  }

  /**
   * Объекты платежных систем
   * @return AbstractWallet[]
   */
  public function getResult()
  {
    $this->wallets = [];
    foreach (Wallet::getWallets(null, $this->activity) as $walletType => $walletName) {
      $this->wallets[$walletType] = Wallet::getObject($walletType);
    }

    return $this->wallets;
  }
}