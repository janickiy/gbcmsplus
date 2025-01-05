<?php

use mcms\common\widget\Select2;
use mcms\payments\assets\MultipleCurrencyAssets;
use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\Module;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use mcms\payments\assets\PaymentsAdminAssets;
use mcms\common\widget\UserSelect2;
use kartik\depdrop\DepDrop;
use mcms\payments\controllers\PaymentsController;

/** @var $userList */
/** @var $select2InitValues */
/* @var $this yii\web\View */
/* @var $model mcms\payments\models\UserPaymentForm */
/* @var $form yii\widgets\ActiveForm */

PaymentsAdminAssets::register($this);
MultipleCurrencyAssets::register($this);
?>

<?php $this->beginBlock('info') ?>
<?php if ($model->isNewRecord): ?>
  <?= Html::beginTag('div', ['id' => 'currencySwitcher', 'class' => 'hidden']) ?>
  <?= Yii::$app->getModule('promo')->api('mainCurrenciesWidget', [
    'type' => 'buttons',
    'containerId' => 'resellerBalanceCurrencySwitcher'
  ])->getResult() ?>
  <?= Html::endTag('div') ?>
<?php endif ?>
<?php $this->endBlock() ?>

<div class="row">
    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title pull-left"><?= $this->title ?></h3>
                <div class="clearfix"></div>
            </div>
            <div class="panel-body">
                <div class="user-payment-form">
                  <?php $form = ActiveForm::begin([
                    'enableAjaxValidation' => true,
                    'validationUrl' => ['validate', 'id' => $model->id],
                    'options' => [
                      'onSubmit' => 'return paymentConfirmConvert.confirm();',
                      'id' => 'userpayment-form',
                      'data' => [
                        'convert-confirm-text' => Yii::_t('payments.payments.convert_confirm_text'),
                        'payment-info-error' => Yii::_t('payments.payments.payment_info_error'),
                        'wallet-data-url' => Url::to(['wallet-data']),
                        'user-detail-url' => Url::to(['user-detail']),
                        'is-new-record' => $model->isNewRecord,
                      ]
                    ]
                  ]); ?>

                  <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>

                  <?php $readOnly = $model->getScenario() != $model::SCENARIO_ADMIN_CREATE ? ['disabled' => 'disabled'] : [] ?>

                    <div class="row">
                        <div class="col-xs-6">
                          <?= $form->field($model, 'user_id')->widget(UserSelect2::class, [
                            'initValueUserId' => $model->user_id,
                            'roles' => Yii::$app->getModule('users')->api('roles')->getMainRoles(),
                            'options' => [
                                'id' => PaymentsController::DEPENDENT_USER_PARAM,
                                'placeholder' => Yii::_t('users.forms.enter_login_or_email') . ':',
                              ] + $readOnly,
                            'readonly' => true,
                          ]) ?>
                        </div>

                        <div class="col-xs-6">
                          <?= empty($readOnly)
                            ? $form->field($model, 'user_wallet_id')
                              ->widget(DepDrop::class, [
                                'data' => $model->user_id ? $model->getWallets(true) : [],
                                'pluginOptions' => [
                                  'initialize' => $model->user_id ? true : false,
                                  'depends' => [PaymentsController::DEPENDENT_USER_PARAM],
                                  'placeholder' => Yii::_t('app.common.not_selected'),
                                  'url' => Url::to(['dependent-wallets', 'filterByCurrency' => false])
                                ],
                              ])
                            : $form->field($model, 'user_wallet_id')->dropDownList(
                              [
                                $model->getWallet()->id => $model->getWallet(true)
                                  ? $model->getWallet()->currency
                                  . ' - ' . $model->getWallet()->getWalletTypeLabel()
                                  . ' (' . $model->getWallet()->getAccountObject()->getUniqueValue() . ')'
                                  : ''
                              ],
                              ['disabled' => 'disabled', 'readonly' => true]
                            ); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-6">
                          <?= $form
                            ->field($model, 'invoice_amount')
                            ->textInput([
                                'type' => 'number',
                                'maxlength' => true,
                                'step' => '0.01',
                              ] + $readOnly) ?>
                        </div>

                      <?php if (Module::canCreatePaymentWithoutEarlyCommission(Yii::$app->user->id)) { ?>
                          <div class="col-xs-6">
                            <?= $form->field($model, 'invoiceType')->widget(Select2::class, [
                              'data' => UserBalanceInvoice::getPaymentInvoiceTypes(),
                              'disabled' => $model->isNewRecord ? false : true,
                            ]); ?>
                          </div>
                      <?php } ?>
                    </div>

                  <?php if ($model->canChangeAmounts()): ?>
                    <div class="row">
                      <div class="col-xs-6"><?= $form
                          ->field($model, 'request_amount')
                          ->textInput([
                              'type' => 'number',
                              'maxlength' => true,
                              'step' => '0.01',
                            ] )
                        ->label($model->getAttributeLabel('request_amount') . ", {$model->currency}")?>
                      </div>
                      <?php if (!$model->isNewRecord) { ?>
                          <div class="col-xs-6"><?= $form
                              ->field($model, 'reseller_paysystem_percent')
                              ->textInput([
                                'type' => 'number',
                                'maxlength' => true,
                                'step' => '0.1',
                              ])
                              ->label($model->getAttributeLabel('reseller_paysystem_percent')) ?>
                          </div>
                      <?php } ?>
                    </div>
                  <?php endif ?>

                  <?php if ($model->isNewRecord) {// не показываем при редактировании, т.к. с баланса уже сняли?>
                      <div class="well balance-after" style="display:none">
                        <?= Yii::_t('user-payments.balance-after') ?>: <span class="balance-after_amount"></span>.
                          <span class="text-danger balance-after_negative-warning" style="display:none">
                          <strong><i class="glyphicon glyphicon-warning"></i> <?= Yii::_t('user-payments.balance-after_negative-warning') ?></strong>
                        </span>
                      </div>
                  <?php } ?>

                    <div id="invoice-file-wrapper" style="display: none">
                      <?= $form->field($model, 'invoice_file')->fileInput([
                        'style' => 'max-width: 100%;',
                      ]) ?>
                    </div>
                    <div id="cheque-file-wrapper" style="display: none">
                      <?= $form->field($model, 'cheque_file')->fileInput([
                        'style' => 'max-width: 100%;',
                      ]) ?>
                    </div>

                  <?= $form->field($model, 'description')->textarea(['rows' => 6])->hint(Yii::_t('payments.description-hint'), ['class' => 'note']) ?>
                  <?= $form->field($model, 'ignore_minimum_amount_check')->checkbox() ?>

                    <hr>
                    <div class="form-group clearfix">
                      <?= Html::submitButton(Yii::_t('app.common.' . ($model->isNewRecord ? 'Create' : 'Save')),
                        ['class' => $model->isNewRecord ? 'btn btn-success pull-right' : 'btn btn-primary pull-right', 'id' => 'submit-button']) ?>
                    </div>

                  <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title pull-left"><?= Yii::_t('payments.info') ?></h3>
                <div class="clearfix"></div>
            </div>
            <div class="panel-body">
                <div id="userpayment-user-info" class="hidden"></div>

            </div>
        </div>
    </div>
</div>
<?php
$requestAmountId = Html::getInputId($model,'request_amount');
$requestAmountName = Html::getInputName($model,'request_amount');
$resellerPaysystemPercentId = Html::getInputId($model,'reseller_paysystem_percent');

$js = <<<JS
$('#userpayment-form').on('change','#{$requestAmountId}',function (event){
    $('#{$resellerPaysystemPercentId}').attr('disabled',true);
})

$('#userpayment-form').on('change','#{$resellerPaysystemPercentId}',function (event){
    let element = $('#{$requestAmountId}');
    element.append('<input type="hidden" name="{$requestAmountName}" value="" step="0.01" aria-invalid="false">')
    element.removeAttr('name');
    element.prop('disabled',true);
})

JS;
$this->registerJs($js);