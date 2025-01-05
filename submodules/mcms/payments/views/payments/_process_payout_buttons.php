<?php

use kartik\widgets\DatePicker;
use mcms\payments\models\UserPaymentForm;
use mcms\payments\models\wallet\Epayments;
use mcms\payments\models\wallet\Wallet;
use rgk\utils\widgets\form\AjaxActiveForm;
use mcms\common\helpers\Html;
use mcms\common\widget\modal\Modal;
use mcms\payments\models\UserPaymentSetting;

/** @var bool $canProcessAuto */
/** @var bool $canProcessMgmp */
/** @var bool $canProcessManual */
/** @var bool $canProcessDelay */
/** @var bool $canProcessPayout */
/** @var bool $canProcessAnnul */
/** @var bool $canProcessCancel */
/** @var \mcms\payments\models\UserPaymentForm $paymentInfo */
/** @var mixed $remoteWalletInsufficientFunds */
/** @var mixed $canSendToMgmp */
/** @var mixed $mgmpErrorText */
/** @var mixed $currencyIsCurrent */
/** @var mixed $delayPaymentInfo */
/** @var UserPaymentForm $autoPaymentInfo */
/** @var mixed $partnerCostHtml */
/** @var mixed $isInvoiceFileShow */
/** @var mixed $pjaxContainer */
/** @var mixed $isCheckFileShow */
/** @var mixed $resellerCommission */
/** @var mixed $formatter */
/** @var mixed $rgkProcess */
/** @var mixed $autoProcess */
/** @var bool $isAlternativePaymentsGridView Включен ли альтернативный вид грида выплат */

$isEpayments = $paymentInfo->wallet_type === Wallet::WALLET_TYPE_EPAYMENTS;
?>
<?php // TODO Зачем здесь дублируется ID button-submit-modal? Вроде не используется даже ?>
<?php // TODO Исправить asPrice на asCurrency ?>
<?php // TRICKY Кнопки обернуты в спан, что бы навесить на него popover. Иначе для кнопок не будут работать title ?>




<?php if ($canProcessAuto) { ?>
  <span class="modal-payment-action-wrapper btn-block">
            <?= Html::button(
              ($remoteWalletInsufficientFunds ? '<span class="glyphicon glyphicon-alert"></span> ' : null)
              . Yii::_t('user-payments.process-auto'),
              [
                'id' => 'button-submit-modal',
                'class' => 'modal-payment-action btn btn-primary btn-block',
                'onclick' => 'yii.confirm("' . Yii::_t('user-payments.are-you-shure-process-auto') . '", function(){ pay("auto") })',
                'disabled' => !$paymentInfo->isAvailableAutopay(),
                'title' => $paymentInfo->getLastError(),
                'data-action-code' => 'process-auto',
              ]
            ) ?>
          </span>
<?php } ?>
<?php if ($canProcessMgmp) { ?>
  <?php
  if ($canSendToMgmp && !$paymentInfo->walletModel->is_mgmp_payments_enabled) {
    $canSendToMgmp = false;
    $mgmpErrorText = Yii::_t('user-payments.error-mgmp-process-disabled');
  }
  ?>
  <span class="modal-payment-action-wrapper btn-block">
          <?= Html::button(
            Yii::_t('user-payments.process-mgmp'),
            [
              'id' => 'button-submit-modal',
              'class' => 'modal-payment-action btn btn-primary btn-block process-mgmp-show',
              'onclick' => 'yii.confirm("' . Yii::_t('user-payments.are-you-shure-process-mgmp') . '", function(){ pay("mgmp") })',
              'disabled' => !$canSendToMgmp,
              'title' => $mgmpErrorText,
              'data-action-code' => 'process-mgmp',
            ]
          ) ?>
          </span>
<?php } ?>

<?php if ($isEpayments) { ?>
  <span class="modal-payment-action-wrapper btn-block well" id="payment-auto-form-wrapper"
        style="display: none;">
    <?php $form = AjaxActiveForm::begin([
      'action' => ['process-auto', 'id' => $paymentInfo->id],
      'messageSuccess' => Yii::_t('payments.user-payments.auto-payment-complete'),
      'messageFail' => '[JS]response.error',
      'enableClientValidation' => true,
      'enableAjaxValidation' => false,
      'isFilesAjaxUpload' => true,
      'ajaxSuccess' => Modal::ajaxSuccess('#' . $pjaxContainer, /** @lang JavaScript */
        "$(document).trigger('process-payout:success')"),
    ]) ?>
    <?= $form->field($autoPaymentInfo, 'autoPayComment')
      ->textarea()
      ->hint(Yii::_t('payments.user-payments.attribute-auto-pay-comment-hint'));
    ?>
    <div class="row">
        <div class="col-sm-6">
              <?= Html::submitButton($autoPaymentInfo::translate('ok'), ['class' => 'btn btn-success btn-block']) ?>
        </div>
        <div class="col-sm-6">
          <button type="button" id="button-autopay-hide" class="btn btn-default btn-block">
            <?= $autoPaymentInfo::translate('cancel') ?>
          </button>
        </div>
      </div>
    <?php $form::end() ?>
  </span>
  <br>
<?php } ?>

<?php if ($canProcessManual) { ?>
  <span class="modal-payment-action-wrapper btn-block">
              <?php $buttonOptions = [
                'id' => 'button-manual-open',
                'class' => 'modal-payment-action btn btn-success btn-block',
                'data-action-code' => 'process-manual',
              ];
              !$isInvoiceFileShow && !$isCheckFileShow && $buttonOptions['onclick'] = 'yii.confirm("'
                . Yii::_t('user-payments.are-you-shure-process-manual')
                . '", function(){ pay("manual") })';
              ?>

              <?= Html::button(
                Yii::_t('user-payments.process-manual'),
                $buttonOptions
              ) ?>
          </span>
<?php } ?>
<?php if ($isInvoiceFileShow || $isCheckFileShow): ?>
  <span class="modal-payment-action-wrapper btn-block well" id="manual-pay-form-wrapper"
        style="display: none;">
              <?php $form = AjaxActiveForm::begin([
                'id' => 'manual-pay-form',
                'action' => ['process-manual', 'id' => $paymentInfo->id],
                'messageSuccess' => Yii::_t('payments.user-payments.maually-processed'),
                'messageFail' => '[JS]response.error',
                'enableClientValidation' => true,
                'enableAjaxValidation' => false,
                'isFilesAjaxUpload' => true,
                'ajaxSuccess' => Modal::ajaxSuccess('#' . $pjaxContainer, /** @lang JavaScript */
                  "$(document).trigger('process-payout:success')"),
              ]) ?>
    <?php if ($isInvoiceFileShow) { ?>
      <?php if ($paymentInfo->invoice_file) { ?>
        <p>
                    <?= Html::a(
                      $paymentInfo::translate('download-invoice'),
                      $paymentInfo->getUploadedFileUrl('invoice_file'), ['target' => '_blank', 'data-pjax' => 0]
                    ); ?>
                    </p>
      <?php } else { ?>
        <?= $form->field($paymentInfo, 'invoice_file')->fileInput(['style' => 'max-width: 100%;']) ?>
      <?php } ?>
    <?php } ?>
    <?php if ($isCheckFileShow) { ?>
      <?= $form->field($paymentInfo, 'cheque_file')->fileInput([
        'style' => 'max-width: 100%;',
      ]) ?>
    <?php } ?>
    <div class="row">
                <div class="col-sm-6">
                  <?= Html::submitButton($paymentInfo::translate('ok'), ['class' => 'btn btn-success btn-block']) ?>
                </div>
                <div class="col-sm-6">
                  <button type="button" id="close-manual" class="btn btn-default btn-block">
                    <?= $paymentInfo::translate('cancel') ?>
                  </button>
                </div>
              </div>
    <?php $form->end() ?>
            </span>
<?php endif ?>
<br>
<?php if ($canProcessDelay) { ?>
  <span class="modal-payment-action-wrapper btn-block">
                <?= Html::button(
                  Yii::_t('user-payments.process-delay'),
                  [
                    'id' => 'button-delay-show',
                    'class' => 'modal-payment-action btn btn-warning btn-block',
                    'disabled' => !$paymentInfo->isAvailableDelay(),
                    'title' => $paymentInfo->getLastError() ?: null,
                  ]
                ) ?>
              </span>
<?php } ?>

<span class="modal-payment-action-wrapper btn-block well" id="payment-delay-form-wrapper"
      style="display: none;">
  <?= $isAlternativePaymentsGridView ? Html::tag('p', UserPaymentSetting::getPayTerms($paymentSetting->pay_terms)) : '' ?>
              <?php $form = AjaxActiveForm::begin([
                'action' => ['process-delay', 'id' => $paymentInfo->id],
                'messageSuccess' => Yii::_t('payments.user-payments.payment-delayed'),
                'messageFail' => '[JS]response.error',
                'enableClientValidation' => true,
                'enableAjaxValidation' => false,
                'isFilesAjaxUpload' => true,
                'ajaxSuccess' => Modal::ajaxSuccess('#' . $pjaxContainer, /** @lang JavaScript */
                  "$(document).trigger('process-payout:success')"),
              ]) ?>
  <?= Html::hiddenInput('type', 'delay') ?>
  <?= $form->field($delayPaymentInfo, 'pay_period_end_date')->widget(DatePicker::class, [
    'options' => [
      // по-умолчанию 10 дней с момента заказа выплаты
      'value' => $delayPaymentInfo->getDefaultDelayTo(),
      'class' => 'btn btn-default btn-sm',
      'style' => 'max-width: 100%;',
    ],
    'pluginOptions' => [
      'format' => 'yyyy-mm-dd',
      'startDate' => $delayPaymentInfo->getMinDelayTo(),
    ],
    'removeButton' => false
  ]) ?>
  <?php if ($isInvoiceFileShow) { ?>
    <?= $form->field($paymentInfo, 'invoice_file')->fileInput([
      'style' => 'max-width: 100%;',
    ]) ?>
  <?php } ?>
  <div class="row">
                  <div class="col-sm-6">
                        <?= Html::submitButton($delayPaymentInfo::translate('ok'), ['class' => 'btn btn-success btn-block']) ?>
                  </div>
                  <div class="col-sm-6">
                    <button type="button" id="button-delay-hide" class="btn btn-default btn-block">
                      <?= $delayPaymentInfo::translate('cancel') ?>
                    </button>
                  </div>
                </div>
  <?php $form->end() ?>
            </span>
<br>
<?php
if ($canProcessCancel) { ?>
  <span class="modal-payment-action-wrapper btn-block">
        <?= Html::button(
          Yii::_t('user-payments.process-cancel'),
          [
            'id' => 'button-submit-modal',
            'class' => 'modal-payment-action btn btn-danger btn-block',
            'onclick' => 'yii.prompt("' . Yii::_t('user-payments.are-you-shure-process-cancel') . '", "' . Yii::_t('user-payments.reason') . '", function(){ pay("cancel", $("#txt1").val()) }, function(){}, function(){ $("#bot2-Msg1").attr("disabled", true); })',
            'disabled' => !$currencyIsCurrent,
            'title' => $currencyIsCurrent ? '' : Yii::_t('payout-info.currency_is_not_current'),
            'data-action-code' => 'cancel',
          ]
        ) ?>
          </span>
<?php } ?>
<?php
if ($canProcessAnnul) { ?>
  <span class="modal-payment-action-wrapper btn-block">
        <?= Html::button(
          Yii::_t('user-payments.process-annul'),
          [
            'id' => 'button-submit-modal',
            'class' => 'modal-payment-action btn btn-danger btn-block',
            'onclick' => 'yii.prompt("' . Yii::_t('user-payments.are-you-shure-process-annul') . '", "' . Yii::_t('user-payments.reason') . '", function(){ pay("annul", $("#txt1").val()) }, function(){}, function(){ $("#bot2-Msg1").attr("disabled", true); })',
            'data-action-code' => 'annul',
          ]
        ) ?>
          </span>
<?php } ?>
<br>
<div style="color: darkblue">
  <?php $resellerCommissionHtml = ''; ?>
  <?php if ($resellerCommission) { ?>
    <?php $resellerCommissionHtml = $formatter->asPrice($resellerCommission->amount, $resellerCommission->currency, ['isPlusVisible' => true])
      . ' (' . $formatter->asPercentSimple($resellerCommission->percent) . ')' ?>
    <?php $partnerCostHtml = $formatter->asPrice($resellerCommission->partnerCost, $resellerCommission->currency, ['isPlusVisible' => true])
      . ' (' . $formatter->asPercentSimple($resellerCommission->partnerCostPercent) . ')' ?>
  <?php } ?>
  <?php $rgkPaysystemCommissionHtml = ''; ?>
  <?php if ($rgkProcess) { ?>
    <?php $rgkPaysystemCommissionHtml = $formatter->asPrice($rgkProcess->rgkPaysystemCommission, $rgkProcess->currency, ['isPlusVisible' => true])
      . ' (' . $formatter->asPercentSimple($rgkProcess->rgkPaysystemPercent) . ')' ?>
    <?php $rgkProcessingCommissionHtml = $formatter->asPrice($rgkProcess->rgkProcessingCommission, $rgkProcess->currency, ['isPlusVisible' => true])
      . ' (' . $formatter->asPercentSimple($rgkProcess->rgkProcessingPercent) . ')' ?>
  <?php } ?>
  <div id="modal-payment-process-auto-hint" style="display:none;">
    <?php if ($autoProcess && $resellerCommission) { ?>
      <?php if ($remoteWalletInsufficientFunds) { ?>
        <p class="text-danger"><span class="glyphicon glyphicon-alert"></span>
          <b><?= Yii::_t('user-payments.autopayout_insufficient_funds') ?></b></p>
      <?php } ?>
      <p>
        <?= Yii::_t('user-payments.reseller_commission') ?>:
        <?= $formatter->decorateValue($resellerCommission->amount, $resellerCommissionHtml) ?><br>
        <?= Yii::_t('user-payments.partner_cost') ?>:
        <?= $formatter->decorateValue($resellerCommission->partnerCost, $partnerCostHtml) ?>
      </p>
      <p>
        <?= Yii::_t('user-payments.reseller_pay_future') ?>:
        <?= $formatter->asPrice($autoProcess->resellerFullCost, $autoProcess->currency) ?>
        <br>
        <?= Yii::_t('user-payments.partner_profit_future') ?>:
        <?= $formatter->asPrice($paymentInfo->amount, $paymentInfo->currency) ?>
      </p>
    <?php } else { ?>
      <?= Yii::_t('user-payments.additional_info_not_available') ?>
    <?php } ?>
  </div>
  <div id="modal-payment-process-mgmp-hint" style="display:none;">
    <?php if ($rgkProcess && $resellerCommission) { ?>
      <?php $resellerCostPercentPart = $rgkProcess->rgkPaysystemPercent - $resellerCommission->percent; ?>
      <p>
        <?= Yii::_t('user-payments.paysystem_commission') ?>:
        <?= $formatter->decorateValue($rgkProcess->rgkPaysystemCommission, $rgkPaysystemCommissionHtml) ?>
        <br>
        <?= Yii::_t('user-payments.reseller_commission') ?>:
        <?= $formatter->decorateValue($resellerCommission->amount, $resellerCommissionHtml) ?>
      </p>
      <p>
        <?= Yii::_t('user-payments.reseller_cost') ?>:
        <?= $formatter->asPrice($rgkProcess->resellerCost, $rgkProcess->currency, [
          'isPlusVisible' => true,
          'decorate' => true,
          'append' => ' ('
            . ($resellerCostPercentPart != 0
              ? $formatter->asPercentSimple($resellerCostPercentPart, null, ['decorate' => true]) . ' + '
              : null)
            . $formatter->asPercentSimple($rgkProcess->rgkProcessingPercent, null, ['decorate' => true]) . ')',
        ]) ?><br>
        <?= Yii::_t('user-payments.partner_cost') ?>:
        <?= $formatter->decorateValue($resellerCommission->partnerCost, $partnerCostHtml) ?>
      </p>
      <p>
        <?= Yii::_t('user-payments.reseller_pay_future') ?>:
        <?= $formatter->asPrice($rgkProcess->resellerFullCost, $rgkProcess->currency) ?>
        <br>
        <?= Yii::_t('user-payments.partner_profit_future') ?>:
        <?= $formatter->asPrice($paymentInfo->amount, $paymentInfo->currency) ?>
      </p>
    <?php } else { ?>
      <?= Yii::_t('user-payments.additional_info_not_available') ?>
    <?php } ?>
  </div>
  <div id="modal-payment-process-manual-hint" style="display:none;">
    <p><?= Yii::_t('user-payments.process_manual_hint') ?></p>
  </div>
  <div id="modal-payment-cancel-hint" style="display:none;">
    <p><?= Yii::_t('user-payments.action_cancel_hint') ?></p>
  </div>
  <div id="modal-payment-annul-hint" style="display:none;">
    <p><?= Yii::_t('user-payments.action_annul_hint') ?></p>
  </div>
  <div id="modal-payment-delay-hint" style="display:none;">
    <p><?= Yii::_t('user-payments.action_delay_hint') ?></p>
  </div>
</div>
