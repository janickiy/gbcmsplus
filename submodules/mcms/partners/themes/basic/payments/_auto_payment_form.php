<?php

use mcms\payments\components\UserBalance;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model mcms\payments\models\PartnerPaymentSettings */
/* @var $form ActiveForm */
/* @var \mcms\payments\models\UserWallet[] $userWallets */

?>
    <div class="payments content__position payments-order autopayment_block <?= $model->isNewRecord ? "hide" : "" ?>">
      <?php
      if(!empty($model->message)){
          echo Html::tag('div',Html::tag('div',Yii::_t("partners.payments.status_of_the_last_autopay_attempt").": ".$model->message,['class'=>'col-md-12 text-danger', 'style'=>['padding-bottom'=>'10px']]),['class'=>'row']);
      }
      ?>
      <?php $form = ActiveForm::begin([
        'id' => 'auto-payment-form',
        'action' => ['auto-payment-form'],
        'enableAjaxValidation' => true,
        //'enableClientValidation' => false
      ]);
      $amountOptions = [];
      if ($model->totality) {
        $amountOptions['disabled'] = 'disabled';
      }
      ?>
  
      <?= $form->field($model, 'amount')->textInput($amountOptions) ?>
      <?= $form->field($model, 'totality')->checkbox() ?>
      
      <?php $options = [];
      if (empty($userWallets)) {
        $options['prompt'] = Yii::_t("partners.payments.you_do_not_have_added_payment_methods");
      }
      ?>
      <?= $form->field($model, 'wallet_id')->dropDownList(ArrayHelper::map($userWallets, 'id', function ($wallet) {
        /** @var $wallet \mcms\payments\models\UserWallet */
        return $wallet->walletType->name . " ({$wallet->currency}) ";
      }), $options) ?>
      <?= $form->field($model, 'invoicing_cycle')->dropDownList(\mcms\payments\models\PartnerPaymentSettings::getInvoicingCycles()) ?>

        <div class="form-group">
          <?= Html::submitButton(Yii::_t('payments.payments-order'), ['class' => 'btn btn-default']) ?>
        </div>
      <?php ActiveForm::end(); ?>
    </div><!-- _auto_payment_form -->
<?php
$messageSuccessfully = Yii::_t('app.common.Saved successfully');
$js = <<<JS
$("#{$form->id}").on('beforeSubmit', function () {
    var yiiform = $(this);
    $.ajax({
            type: yiiform.attr('method'),
            url: yiiform.attr('action'),
            data: yiiform.serializeArray(),
        }
    )
        .done(function(data) {
            if(data.success) {
                // data is saved
                $.notify("{$messageSuccessfully}",{type: 'success'});
            } else if (data.validation) {
                // server validation failed
                yiiform.yiiActiveForm('updateMessages', data.validation, true); // renders validation messages at appropriate places
            } else {
                // incorrect server response
            }
        })
        .fail(function () {
            // request failed
        })

    return false; // prevent default form submission
});

$('#partnerpaymentsettings-totality').on('change',function (element){
    var input = $(this);
    if(input.prop( "checked" )){
        $('#partnerpaymentsettings-amount').attr('disabled','disabled');
        $("#{$form->id}").yiiActiveForm('updateAttribute', 'partnerpaymentsettings-amount', "");
    }else{
        $('#partnerpaymentsettings-amount').removeAttr('disabled');
    }
});


console.log(window.data)

JS;
$this->registerJs($js);