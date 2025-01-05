// перед валидацией, рассчитываем сумму после конвертации
$(document).on('beforeValidateAttribute', '#convert-form', function (e) {
  var $currencyFrom = $('#resellerconvertform-currencyfrom');
  var $currencyTo = $('#resellerconvertform-currencyto');
  var $amountFrom = $('#resellerconvertform-amountfrom');
  var $amountTo = $('#resellerconvertform-amountto');
  var $convertForm = $('#convert-form');

  var $note = $amountFrom.next('.note');

  if (!$currencyFrom.val()) {
    $amountTo.val('');
    changeAmountHint();
    return;
  }
    $.post($convertForm.data('convert-url'), $convertForm.serialize(), function (result) {
      var amount = $currencyFrom.val() === $currencyTo.val() || !result.sum
        ? $amountFrom.val()
        : result.sum.toFixed(2);

      // сумма после конвертации
      $amountTo.val(amount);

      // балланс реса в текущей валюте
      changeAmountHint($currencyFrom.val(), result.balance);
    });
});

// показываем балланс реса в текущей валюте в хинте
function changeAmountHint(currency, amount) {
  var $note = $('#resellerconvertform-amountfrom').next('.note');

  if (currency === undefined) {
    $note.addClass('hidden');
    return;
  }
  $note.find('span').html(amount + ' ' + currency);
  $note.removeClass('hidden');
}