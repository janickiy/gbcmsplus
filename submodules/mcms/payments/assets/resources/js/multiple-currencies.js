$(function () {
  var $partnerSettings = $('#partner-settings form');

  $partnerSettings.length && $partnerSettings.dirtyForms();

  $('#resellerBalanceCurrencySwitcher').on('mainCurrencyChanged', function () {
    var $balance = $('#balance')
      , $paymentFormAmount = $('#userpayment-form').find('#userpaymentform-invoice_amount:first')
      , $userInvoices = $('#user-invoices');

    $balance.length && $.pjax.reload({container: $balance});
    $paymentFormAmount.length && $paymentFormAmount.val('').trigger('blur');
    $userInvoices.length && $.pjax.reload({container: $userInvoices});

    //$userPaymentSettings.length && document.location.reload();
    $partnerSettings.length && document.location.reload();
  }).on('mainCurrencyChange', function (e) {
    if ($partnerSettings.length && $partnerSettings.dirtyForms('isDirty')) {
      if (!confirm($(this).data('confirm-text'))) {
        e.preventDefault();
        return;
      }

      $partnerSettings.dirtyForms('setClean');
    }
  });
});