var paymentConfirmConvert;
var userBalance;

$(function () {
  var $userPayment = $('#userpaymentform-user_id')
    , $userPaymentWallet = $('#userpaymentform-user_wallet_id')
    , paymentId = $('#userpaymentform-id').val()
    , $userPaymentForm = $('#userpayment-form')
    , $userPaymentInfo = $('#userpayment-user-info')
    , $userPaymentAmount = $('#userpaymentform-invoice_amount')
    , $currencySwitcher = $('#currencySwitcher')
    , $balanceAfter = $('.balance-after');


  $userPaymentInfo.on('checkUserDetail', function (e, $userPayment, $userPaymentForm) {
    var userId = $userPayment.val();
    if (userId == '') {
      $(this).addClass('hidden');
      return;
    }

    $
      .ajax({
        url: $userPaymentForm.data('user-detail-url'),
        data: {userId: $userPayment.val(), walletId: $userPaymentWallet.val(), paymentId: paymentId}
      })
      .done(function (response) {
        paymentConfirmConvert.updatePaymentInfo(response.data);

        // Отображение полей для загрузки акта и квитанции
        response.data.isInvoiceFieldShow
          ? showField('#invoice-file-wrapper')
          : hideField('#invoice-file-wrapper');

        response.data.isChequeFieldShow
          ? showField('#cheque-file-wrapper')
          : hideField('#cheque-file-wrapper');

        userBalance = response.data.balance;

        this.removeClass('hidden');
        $('#submit-button').prop('disabled', response.success == false);
        if (response.success == false) {
          this.html(response.error);
          $userPaymentForm.on('submit', function (e) {
            e.preventDefault();
          });
          return;
        }
        this.html(response.data.view);
        $userPaymentForm.off('submit');
        if (response.data.canUseMultipleCurrenciesBalance) {
          $currencySwitcher.removeClass('hidden');
        } else {
          $currencySwitcher.addClass('hidden');
        }

        refreshBalanceInfo();

      }.bind($(this)));
  });

  $userPaymentWallet.on('change', function (e) {
    $userPaymentAmount.trigger('blur');
    $userPaymentInfo.trigger('checkUserDetail', [$userPayment, $userPaymentForm]);
  });
  $userPaymentInfo.trigger('checkUserDetail', [$userPayment, $userPaymentForm]);

  $currencySwitcher.on('mainCurrencyChanged', function () {
    $userPaymentInfo.trigger('checkUserDetail', [$userPayment, $userPaymentForm]);
  });

  $userPaymentAmount.on('change paste keyup', function () {
    refreshBalanceInfo();
  });

  $userPayment.on('change', function () {
    if (!$balanceAfter) {
      return;
    }
    $balanceAfter.hide();
  });

  // Отобразить поле
  function showField(wrapperSelector) {
    var $wrapper = $(wrapperSelector);
    $wrapper.find('input').prop('disabled', false);
    $wrapper.show();
  }

  // Скрыть поле
  function hideField(wrapperSelector) {
    var $wrapper = $(wrapperSelector);
    $wrapper.hide();
    $wrapper.find('input').prop('disabled', true);
  }

  // блок с балансом после выплаты
  function refreshBalanceInfo() {
    if (!$balanceAfter) {
      return;
    }

    if (!$userPaymentWallet.val()) { // не выбран кошелек
      $balanceAfter.hide();
      return;
    }

    var inputVal = $userPaymentAmount.val();

    if (inputVal > 0) {

      var newBalance = userBalance.amount - inputVal;
      $balanceAfter.find('.balance-after_amount').html(
        newBalance < -999999999
          ? '-&#8734;'
          : rgk.formatter.asCurrency(newBalance, userBalance.currency)
      );

      if (newBalance < 0) {
        $balanceAfter.find('.balance-after_negative-warning').show();
      } else {
        $balanceAfter.find('.balance-after_negative-warning').hide();
      }

      $balanceAfter.show();
    } else {
      $balanceAfter.hide();
    }
  }
});