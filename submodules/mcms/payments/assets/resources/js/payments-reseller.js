$(function () {
  var $userPaymentInfo = $('#userpayment-user-info')
    , $userPaymentWallet = $('#userpaymentform-user_wallet_id')
    , $userPaymentForm = $('#userpayment-form')
    , paymentId = $('#userpaymentform-id').val()
    , $userPaymentAmount = $('#userpaymentform-invoice_amount')
    , $user = $('#userpaymentform-user_id');

  $userPaymentInfo.on('checkUserDetail', function (e, $user, $userPaymentForm, paymentId) {
    var userId = $user.val();
    if (userId == '') {
      $(this).addClass('hidden');
      return;
    }

    $.ajax({
      url: $userPaymentForm.data('user-detail-url'),
      data: {userId: $user.val(), walletId: $userPaymentWallet.val(), paymentId: paymentId}
    }).done(function (response) {
      paymentConfirmConvert.updatePaymentInfo(response.data);

      this.removeClass('hidden');
      $('button[type=submit]').prop('disabled', response.success == false);
      if (response.success == false) {
        this.html(response.error);
        $userPaymentForm.on('submit', function (e) {
          e.preventDefault();
        });
        return;
      }
      this.html(response.data.view);
      $userPaymentForm.off('submit');

      $userPaymentAmount.trigger('blur');
    }.bind($(this)));
  });

  $userPaymentWallet.on('change', function () {
    $userPaymentInfo.trigger('checkUserDetail', [$user, $userPaymentForm, paymentId]);
  }).trigger('select2:select');

  $userPaymentInfo.trigger('checkUserDetail', [$user, $userPaymentForm, paymentId]);
});