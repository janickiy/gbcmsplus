<?php
use kartik\depdrop\DepDrop;
use mcms\payments\assets\MultipleCurrencyAssets;
use mcms\payments\components\widgets\assets\UserSettingsAsset;
use mcms\payments\components\widgets\PartnerSettings;
use mcms\payments\controllers\UsersController;
use mcms\payments\models\UserPaymentSetting;
use mcms\payments\Module;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var UserPaymentSetting $model */
/** @var $currencyList */
/** @var bool $canChangeCurrency */
/** @var string|null $canChangeCurrencyError */
/** @var bool $canChangeWallet */

UserSettingsAsset::register($this);
$model->canUseMultipleCurrenciesBalance() && MultipleCurrencyAssets::register($this);

$currencyOptions = !$canChangeCurrency || $model->canUseMultipleCurrenciesBalance()
  ? ['disabled' => true, 'title' => $canChangeCurrencyError]
  : [];
$walletOptions = $canChangeWallet ? [] : ['disabled' => true];
?>
<div id="partner-settings" class="container col-xs-12 well">
  <?php $form = ActiveForm::begin([
    'action' => Url::to([PartnerSettings::URL . 'update-partner']),
    'id' => 'currency-wallet',
    'options' => [
      'data-currency-id' => UsersController::DEPEND_CURRENCY_PARAM,
    ]
  ]) ?>

  <?= $form->field($model, 'currency')->dropDownList($model->currencyList, [
      'class' => 'form-control',
      'id' => UsersController::DEPEND_CURRENCY_PARAM,
      'prompt' => Yii::_t('app.common.choose'),
      'name' => 'currency'
    ] + $currencyOptions) ?>

  <?= $form->field($model, 'wallet_type')->widget(DepDrop::class, [
    'data' => $model->currency ? $model->getWalletDropDown($model->currency) : [],
    'name' => UsersController::DEPEND_WALLET_PARAM,
    'options' => [
      'id' => UsersController::DEPEND_WALLET_PARAM,
      'name' => UsersController::DEPEND_WALLET_PARAM,
      'prompt' => Yii::_t('app.common.choose'),
      'onchange' => '$(function() {
        $("#currency-wallet").trigger("submit")
      })',
    ],
    'pluginOptions' => [
      'depends' => [UsersController::DEPEND_CURRENCY_PARAM],
      'placeholder' => Yii::_t('app.common.choose'),
      'url' => Url::to([PartnerSettings::URL . 'dependent-wallets']),
    ],
    'pluginEvents' => [
      'depdrop.afterChange' => 'function(event, id, value) {
          $("#' . UsersController::DEPEND_WALLET_PARAM . '").trigger("change")
        }',
    ]
  ] + $walletOptions) ?>
  <?php ActiveForm::end() ?>

  <div id="user-payment-settings-container">
    <?= $this->render('_partner_settings_form', compact('model', 'canChangeCurrency', 'canChangeWallet')) ?>
  </div>
</div>
