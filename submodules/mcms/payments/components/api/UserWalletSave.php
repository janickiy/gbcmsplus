<?php

namespace mcms\payments\components\api;

use mcms\common\module\api\ApiResult;
use mcms\payments\models\UserPaymentSetting;
use mcms\payments\models\UserWallet as UserWalletModel;
use mcms\payments\models\wallet\Wallet;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;

/**
 * Class UserWalletSave
 * @package mcms\payments\components\api
 */
class UserWalletSave extends ApiResult {


  private $userId;
  private $currency;
  private $walletType;
  private $userPaySettings;
  private $walletAccount;


  /**
   * @param array $params
   */
  public function init($params = [])
  {
    if (!$this->userId = ArrayHelper::getValue($params, 'userId')) {
      throw new InvalidParamException('userId required');
    }
    if (!$this->currency = ArrayHelper::getValue($params, 'currency')) {
      throw new InvalidParamException('currency required');
    }
    if (!$this->walletType = ArrayHelper::getValue($params, 'walletType')) {
      throw new InvalidParamException('walletType required');
    }
    if (!$this->walletAccount = ArrayHelper::getValue($params, 'walletAccount')) {
      throw new InvalidParamException('walletAccount required');
    }
    $this->userPaySettings = UserPaymentSetting::fetch($this->userId);

    // Проверка чтобы юзер просто так не поменял себе валюту
    if (
      !$this->userPaySettings->isNewRecord
      && !empty($this->userPaySettings->currency)
      && $this->currency != $this->userPaySettings->currency
    ) {
      throw new InvalidParamException('invalid wallet currency');
    }

  }

  /**
   * @return bool
   */
  public function getResult()
  {
    $this->userPaySettings = UserPaymentSetting::fetch($this->userId);
    if ($this->userPaySettings->isNewRecord || empty($this->userPaySettings->currency)) {
      $this->userPaySettings->setScenario(UserPaymentSetting::SCENARIO_PARTNER_UPDATE);
      $this->userPaySettings->currency = $this->currency;
      if (!$this->userPaySettings->save()) return false;
    }

    $model = UserWalletModel::findByUserAndType($this->userId, $this->walletType)->one();

    if (!$model) {
      $model = new UserWalletModel([
        'user_id' => $this->userId,
        'wallet_type' => $this->walletType,
        'currency' => $this->currency
      ]);
    }

    $walletAccount = Wallet::getObject($model->wallet_type, $this->walletAccount);

    $model->wallet_account = (string)$walletAccount;

    return $walletAccount->validate() && $model->save();
  }

}