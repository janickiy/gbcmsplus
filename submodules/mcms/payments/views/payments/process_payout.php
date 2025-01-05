<?php

use mcms\common\helpers\Html;
use mcms\common\widget\modal\Modal;
use yii\helpers\Url;
use yii\web\JsExpression;

/** @var \mcms\payments\models\UserPaymentForm $paymentInfo */
/** @var \mcms\payments\models\UserPaymentForm $delayPaymentInfo */
/** @var \mcms\payments\models\UserPaymentForm $autoPaymentInfo */
/** @var \yii\web\View $this */
/** @var boolean $canSendToMgmp */
/** @var boolean $currencyIsCurrent */
/** @var bool $remoteWalletInsufficientFunds */
/** @var string $mgmpErrorText */
/** @var \mcms\common\AdminFormatter $formatter */
/* @var \mcms\payments\models\PartnerCompany $partnerCompany */
/* @var bool $showModalCompany */

$canProcessAuto = Html::hasUrlAccess(['/payments/payments/process-payout']);
//$canProcessMgmp = Html::hasUrlAccess(['/payments/payments/process-payout']);
$canProcessMgmp = false; // TRICKY было актуально при реселлинге партнерки, сейчас не нужно
$canProcessManual = Html::hasUrlAccess(['/payments/payments/process-manual']);
$canProcessDelay = Html::hasUrlAccess(['/payments/payments/process-delay']);
$canProcessPayout = Html::hasUrlAccess(['/payments/payments/process-payout']);
$canProcessAnnul = Html::hasUrlAccess(['/payments/payments/process-payout']);
$canProcessCancel = Html::hasUrlAccess(['/payments/payments/process-payout']);
$canProcess = $canProcessAuto || $canProcessMgmp || $canProcessManual || $canProcessDelay
  || $canProcessPayout || $canProcessAnnul || $canProcessCancel;

$formatter = Yii::$app->formatter;
$resellerCommission = $paymentInfo->calcResellerCommission();
$autoProcess = $paymentInfo->calcAutoProcess();
$rgkProcess = $paymentInfo->calcRgkProcess();

$payprocessUrl = Url::to(['/payments/payments/process-payout', 'id' => $paymentInfo->id]);
// TRICKY Ниже есть дубли тригера события
$ajaxSuccess = Modal::ajaxSuccess(
  '#' . $pjaxContainer,
  new JsExpression('
    notifyInit(null, response.data.message, response.data.success);
    if (response.data.success) $(document).trigger("process-payout:success");
  ')
);

$isCheckFileShow = $paymentInfo->walletModel->is_check_file_show;
$isInvoiceFileShow = $paymentInfo->walletModel->is_invoice_file_show;

$this->registerJs(<<<JS
  var body = $('body');
  if (!body.hasClass('confirm-binded')) {
    $(document).on('keyup', '#txt1', function(event) {
      var disabled = false;
      if ($(this).val() == '') {
        disabled = true;
      }
      var btnYes = $("#bot2-Msg1");
      btnYes.attr("disabled", disabled);
      if (!disabled && event.keyCode == 13) {
        btnYes.click();
      } 
    });
  
  
    // хак для мозилы, инпут не дается
    $(document).on('focus', '#MsgBoxBack input', function() {
      $('#modalWidget').css({overflowY: 'hidden'});
    });
    $(document).on('blur', '#MsgBoxBack input', function() {
      $('#modalWidget').css({overflowY: 'auto'});
    });
    body.addClass('confirm-binded');
  }

  function pay(type, message) {
    $.ajax({
      url: "$payprocessUrl",
      type: 'post',
      data: { type: type, message: message },
      success: $ajaxSuccess
    })
  }
  $('.modal-payment-action-wrapper').popover({
     trigger: 'hover',
     placement: function() {
       return $(window).outerWidth() < 1000 ? 'top' : 'left';
     },
     html: true,
     delay: 100,
     container: '.modal-body',
     content: function() {
       var actionCode = $(this).find('.modal-payment-action').data('action-code');
      return $('#modal-payment-' + actionCode + '-hint').html();
     }
  });
JS
);
if ($isCheckFileShow || $isInvoiceFileShow) {
  $this->registerJs(<<<JS
  $('#button-manual-open').click(function(e) {
    e.preventDefault();
    $('#manual-pay-form-wrapper').show();
  });
  
  $('#close-manual').click(function(e) {
    e.preventDefault();
    $('#manual-pay-form-wrapper').hide();
  });
JS
  );
}

$this->registerJs(<<<JS
  $('#button-delay-show').click(function(e) {
    e.preventDefault();
    $('#payment-delay-form-wrapper').show();
  });
  
  $('#button-delay-hide').click(function(e) {
    e.preventDefault();
    $('#payment-delay-form-wrapper').hide();
  });
JS
);

$this->registerJs(<<<JS
  $('#button-submit-auto').click(function(e) {
    e.preventDefault();
    $('#payment-auto-form-wrapper').show();
  });
  
  $('#button-autopay-hide').click(function(e) {
    e.preventDefault();
    $('#payment-auto-form-wrapper').hide();
  });
JS
);
?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">
        <span class="fa-lg fa-fw fa fa-info-circle"></span>
      <?= $this->title ?>
    </h4>
</div>

<div class="modal-body">
    <div class="row">
        <div class="col-sm-<?= $canProcess ? 8 : 12 ?>">
          <?= $this->render('_payment-details', ['payment' => $paymentInfo, 'partnerCompany' => $partnerCompany,
            'showModalCompany' => false, 'showWalletDetails' => true]) ?>
        </div>
      <?php if ($canProcess) { ?>
        <?php // TODO Переменные, которые используются в _process_payout_buttons, но не используются в текущей вьюхе,
        // надо перенести в _process_payout_buttons ?>
          <div class="col-sm-4">
            <?= $this->render('_process_payout_buttons', compact(
              'canProcessAuto',
              'canProcessMgmp',
              'canProcessManual',
              'canProcessDelay',
              'canProcessPayout',
              'canProcessAnnul',
              'canProcessCancel',
              'paymentInfo',
              'remoteWalletInsufficientFunds',
              'canSendToMgmp',
              'mgmpErrorText',
              'currencyIsCurrent',
              'delayPaymentInfo',
              'partnerCostHtml',
              'isInvoiceFileShow',
              'isCheckFileShow',
              'resellerCommission',
              'pjaxContainer',
              'formatter',
              'autoProcess',
              'rgkProcess',
              'paymentSetting',
              'isAlternativePaymentsGridView',
              'autoPaymentInfo'
            )) ?>
          </div>
      <?php } ?>
    </div>
</div>