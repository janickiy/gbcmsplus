(function () {
  var ww = $(window).outerWidth();

  $(window).resize(function () {
    ww = $(window).outerWidth();
  });

  $('.selectpicker').selectpicker();

  $('#change_theme').on('change', function (e) {
    event.stopPropagation();
    $('body').removeClass('cerulean blue amethyst alizarin orange green grey').addClass($(this).val());
  });

  $('.partners-merchant > li:not(.disabled) input').on('change', function () {
    var wall_set = $('.wallet_settings');
    wall_set.hide().filter('[data-show="' + $(this).val() + '"]').show();
  });

  $(document).on('change', '.radio__filter > div >label:not(.disabled) input, .radio__filter > li:not(.disabled) input', function (e, flag) {

    $(this).parent()
      .addClass('active')
      .parents('.radio__filter')
      .find('label')
      .not($(this).parent())
      .removeClass('active');
    if (!flag) {
      if (ww < 1000) {
        var s_t = $('.profile').offset().top - 66;
      } else {
        var s_t = $('.profile').offset().top;
      }
      $('html, body').animate({scrollTop: s_t}, 500);
    }

  });

  $('.partners-currency > div >label:not(.disabled) input').on('change', function(e, flag) {

    var all_merchants = $('.partners-merchant li');
    var visible_merchants = all_merchants.hide().filter('[data-show="' + $(this).val() + '"]').fadeIn(300);
    visible_merchants.first().find('input').prop('checked', true).trigger('change', [true]);
  });


  $('.wire_tf-mode input').on('change', function () {
    $('.wire_tf').find('#' + $(this).val()).show().siblings().hide();
  });

  $('.partners-currency label.active input').trigger('change', [true]);
})();