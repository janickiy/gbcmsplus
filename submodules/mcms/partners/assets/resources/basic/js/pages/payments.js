$('.payments-status__toggle:not(.disabled)')
  .on('click', function () {
    $(this).add('.payments-status').toggleClass('on');
    $('.autopayment_block').toggleClass('hide');
    $(this).trigger('toggle-auto-payments');
  })
  .on('toggle-auto-payments', function () {
    var data = $(this).data();
    $.post($(this).hasClass('on') ? data['enableUrl'] : data['disableUrl']);
  })
;
