$(function () {
  $('#currency-wallet').on('submit', function (e) {
    e.preventDefault();
    var $container = $('#user-payment-settings-container')
      , $loading = $('#user-payment-settings-container-loading');

    $loading.removeClass('hidden');
    $.get($(this).attr('action'), $(this).serializeArray())
      .done(function (response) {
        $container.html(response);
        $loading.addClass('hidden');
      })
  });

  $('[data-toggle=tooltip]', '#user-payment-settings').tooltip();
});