$(function() {

  $('form').on('beforeValidate', function(){ // Костыль тобы заглушить yii-форму
    $(this).find('[type=submit]:first').prop('disabled', true);
    return true;
  });

  $('form').on('beforeSubmit', function(e) {
    e.preventDefault();
    e.stopPropagation();

    return false;
  });

  $(document).on("captchaValid", function(e, isValid) {
    document.querySelectorAll('.g-recaptcha').forEach(function(v, i) {
      var el = $(v);

      if (isValid) {
        el.parent().removeClass('has-error');
        el.siblings('.help-block').text("");
        return ;
      }

      el.parent().addClass('has-error');
      el.siblings('.help-block').text(window.recaptchaRequiredCaption);
    });
  });

  $('form').on('afterValidate', function (e) {
    // Если валидация формы прошла успешно, отправляем запрос
    var $form = $(this),
      id = $form.attr('id').replace(/\-/g, ''),
      data = $form.serializeArray(),
      $recapcha = $('#recapcha-' + $form.attr('id')),
      $recaptchaInput = $('#' + id + '-captcha');

    if ($recaptchaInput.length > 0 && $recaptchaInput.next().hasClass('g-recaptcha')) {
      if ($('#g-recaptcha-response').val() === '' &&
        ($('#g-recaptcha-response-1').length === 0 || $('#g-recaptcha-response-1').val() === '')) {
        $recaptchaInput.parent().addClass('has-error');
        $recaptchaInput.siblings('.help-block').text(window.recaptchaRequiredCaption);
      } else {
        $recaptchaInput.parent().removeClass('has-error');
        $recaptchaInput.siblings('.help-block').text();
      }
    }

    if (!$form.find('.has-error').length) {
      $.ajax({
        url: $form.attr('action'),
        type: "post",
        dataType: "json",
        data: data
      })
        .done(function (res) {
          $form
            .find('.form-group')
            .removeClass('has-error')
            .find('.help-block').empty()
          ;
          if (res.success == false) {
            var errors = res.error;
            if (errors != '') {
              $.each(errors, function (field, error) {
                $form
                  .find('.field-' + id + '-' + field)
                  .addClass('has-error')
                  .removeClass('has-success')
                  .find('.help-block')
                  .html(error)
                  .show(0)
                ;
              });
            }

            if (res.error.useCaptcha) {
              if (res.success === false || $('#' + id + '-captcha').val() != '') {
                document.querySelectorAll('.g-recaptcha').forEach(function(v, i) {
                  grecaptcha.reset(i);
                });
                $('#loginform-captcha').val('');
                window.isCaptchaValid = false;
                $recaptchaInput.parent().removeClass('has-error');
                $recaptchaInput.siblings('.help-block').text();
              } else if ($recapcha.is(':empty')) {
                $recapcha.empty();
                var widgetId = grecaptcha.render('recapcha-' + $form.attr('id'), {
                  'sitekey': $recapcha.data('site-key'),
                  callback: function (response) {
                    $('#' + id + '-capcha').val(response);
                  }
                });
                $recapcha.attr('data-cid', widgetId);
              }
            }
          } else {
            $('.close:visible').filter(function () {
              return $(this).parent().css('opacity') == '1';
            }).trigger('click');
            $('.success-title').html(res.data.title ? res.data.title : '');
            $('.success-subtitle').html(res.data.subtitle ? res.data.subtitle : '');
            $('.success-action').html(res.data.action ? res.data.action : '');
            $('.success-message').html(res.data.message ? res.data.message : '');

            if (res.data.error != undefined) $('.modal-ok').removeClass().addClass('modal-error fa fa-close');

            if (res.data.title != undefined) $('#success-modal-button').trigger('click');

            if (res.data.redirectUrl) window.location = res.data.redirectUrl;

            $form.trigger('reset');

            // Если на странице есть recaptcha
            if (window.___grecaptcha_cfg.count > 0) {
              grecaptcha.reset();
            }
          }
          $form.find('[type=submit]:first').prop('disabled', false);
        });
    } else {
      $form.find('[type=submit]:first').prop('disabled', false);
    }
  });

  var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
      sURLVariables = sPageURL.split('&'),
      sParameterName,
      i;

    for (i = 0; i < sURLVariables.length; i++) {
      sParameterName = sURLVariables[i].split('=');

      if (sParameterName[0] === sParam) {
        return sParameterName[1] === undefined ? true : sParameterName[1];
      }
    }
  };

  var activationCode = getUrlParameter('activationCode');
  if (activationCode) {
    $.ajax({url: '/users/api/activate/?code=' + activationCode})
      .done(function (res) {
        if (res.success) {
          $('.success-title').html(res.data.title ? res.data.title : '');
          $('.success-subtitle').html(res.data.subtitle ? res.data.subtitle : '');
          $('.success-action').html(res.data.action ? res.data.action : '');
          $('.success-message').html(res.data.message ? res.data.message : '');

          if(res.data.error != undefined) $('.modal-ok').removeClass().addClass('modal-error fa fa-close');

          $('#success-modal-button').trigger('click');
        }
      });
  }

  var token = getUrlParameter('token');
  if (token != undefined) {

    $.ajax({url: '/users/api/valid-token/?token=' + token})
      .done(function (res) {
        if (res.success == true) {
          $('.reset-password').addClass('open');
          var $resetPasswordForm = $('.reset-password-form');
          $resetPasswordForm.attr('action', $resetPasswordForm.attr('action') + '?token=' + token)

          $('#reset-modal-button').trigger('click')
        } else {
          $('#fail-modal')
            .find('.fail-subtitle').html(res.error.subtitle ? res.error.subtitle : '').end()
            .find('.fail-title').html(res.error.title ? res.error.title : '').end()
            .find('.fail-message').html(res.error.message ? res.error.message : '')
          ;
          $('#fail-modal-button').trigger('click');
        }
      });
  }

  var action = getUrlParameter('action');

  if (getUrlParameter('refId') != undefined || (action != undefined && action == 'signup')) {
    if ($("#reg-modal").length > 0) {
      UIkit.modal("#reg-modal").show();
    } else {
      $('.register-modal-button').trigger('click');
    }
  }

  if (action != undefined && action == 'login') {
    if ($("#login-modal").length > 0) {
      UIkit.modal("#login-modal").show();
    } else {
      $('.login-modal-button').trigger('click');
    }
  }

  if (action != undefined && action == 'request-password-reset') {
    if ($("#remember-modal").length > 0) {
      UIkit.modal("#remember-modal").show();
    } else {
      $('.request-password-modal-button').trigger('click');
    }
  }
});
