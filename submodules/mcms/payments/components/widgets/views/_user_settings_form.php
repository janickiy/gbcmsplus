<?php
use kartik\helpers\Html;
use kartik\widgets\Spinner;
use mcms\common\form\AjaxActiveForm;
use mcms\common\helpers\ArrayHelper;
use mcms\payments\Module;
use mcms\payments\components\widgets\PartnerSettings;
use mcms\payments\models\UserPaymentSetting;

/** @var \mcms\common\web\View $this */
/** @var \mcms\payments\models\UserPaymentSetting $model */
/** @var array $currencyOptions */
/** @var bool $canViewAdditionalParameters */
/** @var bool $canCreatePaymentWithoutEarlyCommission */
/** @var bool $isAlternativePaymentsGridView вкючен ли альтернативный вид грида. влияет на вывод настройки pay_terms */
/** @var bool $isPartner Партнер? */

$ajaxComplete = '
  function(){
    ModalWidget.empty("#modalWidget");
    $.pjax.reload({container:"#user-payment-settings-pjax-block"});
  }
';
?>
<?php $form = AjaxActiveForm::begin([
  'id' => 'user-payment-settings',
  'action' => [PartnerSettings::URL . 'update-settings', 'id' => $model->user_id],
  'ajaxComplete' => $ajaxComplete,
]) ?>

<?php if(!$model->canUseMultipleCurrenciesBalance()): ?>
  <?= $form->field($model, 'currency')->dropDownList($currencyList, [
      'class' => 'form-control',
      'prompt' => Yii::_t('app.common.choose'),
      'unselect' => 'usd'
    ] + $currencyOptions)
    ->hint(
    (ArrayHelper::getValue($model->oldAttributes, 'currency', $model->currency) != $model->currency
      ? Html::tag('div', Html::icon('alert', ['style' => 'color:red'])
        . ' ' . Yii::_t('payments.events.user-currency-changed-to', ['currency' => $model->oldAttributes['currency']]))
      : null
    )
  )
  ?>
<?php else: ?>
  <?= $form->field($model, 'currency')->hiddenInput()->label(false) ?>
<?php endif; ?>

<?= Spinner::widget([
  'preset' => 'small',
  'options' => [
    'class' => 'hidden',
    'id' => 'user-payment-settings-container-loading'
  ]
]) ?>

<?php if ($model->isAttributeSafe('referral_percent')) { ?>
    <?= $form->field($model, 'referral_percent') ?>
<?php } ?>

<?php if ($model->isAttributeSafe('visible_referral_percent')) { ?>
    <?= $form->field($model, 'visible_referral_percent') ?>
<?php } ?>

<?php // Если пользователь, для которого производится настройка не может создавать выплаты без комиссии за запрос,
// отображаем поле для указания процента комиссии за создание выплаты ?>
<?= Yii::$app->user->can(Module::PERMISSION_CAN_CHANGE_PAYMENT_EARLY_COMMISSION) && !$canCreatePaymentWithoutEarlyCommission
  ? $form->field($model, 'early_payment_percent') : null ?>

<?php if ($canViewAdditionalParameters): ?>
  <?= $form->field($model, 'is_disabled')->checkbox() ?>
  <?= $form->field($model, 'is_wallets_manage_disabled')->checkbox() ?>
<?php endif ?>

<?php if ($isAlternativePaymentsGridView && $isPartner): ?>
  <?= $form->field($model, 'pay_terms')->dropDownList(UserPaymentSetting::getPayTerms(), [
    'class' => 'form-control',
  ]) ?>
<?php endif; ?>

<hr>
<div class="form-group">
  <?= Html::submitButton(Yii::_t('app.common.Save'), ['class' => 'btn btn-primary pull-right']) ?>
  <div class="clearfix"></div>

</div>

<?php AjaxActiveForm::end() ?>
