<?php

namespace mcms\payments\components\api;

use mcms\payments\models\UserWallet;
use Yii;
use mcms\common\module\api\ApiResult;
use mcms\payments\models\UserPaymentSetting;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class UserSettingsData extends ApiResult
{

  private $userId;
  /** @var  UserPaymentSetting */
  private $userPaymentSettings;

  function init($params = [])
  {
    if (!$this->userId = ArrayHelper::getValue($params, 'userId')) {
      $this->addError('userId required');
    }
  }

  /**
   * @return UserPaymentSetting
   */
  public function getResult()
  {
    if ($this->getErrors()) {
      return false;
    }
    if ($this->userPaymentSettings !== null) {
      return $this->userPaymentSettings;
    }
    if (!$this->userPaymentSettings = UserPaymentSetting::findOne(['user_id' => $this->userId])) {
      $this->userPaymentSettings = new UserPaymentSetting([
        'user_id' => $this->userId,
        'scenario' => UserPaymentSetting::SCENARIO_ADMIN_CREATE
      ]);
      if (!$this->userPaymentSettings->save()) {
        return false;
      }
    }
    return $this->userPaymentSettings;
  }

  public function getReferralPercent()
  {
    $settings = $this->getResult();

    return empty($settings) ? UserPaymentSetting::getReferralPercentSettingsValue() : $settings->referral_percent;
  }

  public function getVisibleReferralPercent()
  {
    $settings = $this->getResult();

    return empty($settings) ? UserPaymentSetting::getVisibleReferralPercentSettingsValue() : $settings->visible_referral_percent;
  }

  /**
   * Назначить партнеру программу холдов
   * @param $holdProgramId
   * @return bool
   */
  public function setHoldProgramId($holdProgramId)
  {
    $settings = $this->getResult();
    $settings->setScenario($settings::SCENARIO_SET_HOLD_PROGRAM_ID);
    $settings->hold_program_id = $holdProgramId;
    return $settings->save();
  }

  /**
   * Удалить у партнера программу холдов
   * @return bool
   */
  public function unsetHoldProgramId()
  {
    $settings = $this->getResult();
    $settings->setScenario($settings::SCENARIO_SET_HOLD_PROGRAM_ID);
    $settings->hold_program_id = null;
    return $settings->save();
  }

  /**
   * @param UserWallet $userWallet
   * @return bool
   */
  public function canRequestPayments(UserWallet $userWallet)
  {
    return $this->getResult()->canRequestPayments($userWallet);
  }

  public function canUseMultipleCurrenciesBalance()
  {
    $settings = $this->getResult();
    return $settings ? $settings->canUseMultipleCurrenciesBalance() : false;
  }

  public function getUserCurrencyLinkParams()
  {
    return ['/' . Yii::$app->getModule('payments')->id . '/users/get-user-currency'];
  }

  /**
   * @param ActiveRecord $model
   * @param $column
   * @return \yii\db\ActiveQuery
   */
  public function hasOne(ActiveRecord $model, $column)
  {
    return $this->hasOneRelation($model, UserPaymentSetting::class, ['user_id' => $column]);
  }
}