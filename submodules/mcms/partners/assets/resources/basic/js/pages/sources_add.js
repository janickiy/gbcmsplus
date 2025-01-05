$(function() {
  var $form = $('#sourceForm'),
      $stepNumber = $form.find('input[name=stepNumber]'),
      $sourceIdInput = $form.find('input#sourceId');

  var promoStep = new Steps(
    [
      {
        ButtonText: $form.data('next'),
        beforeAction: function() {
          return true;
        },
        afterAction: function(indx) {
          $stepNumber.val(indx);
          $('.change__stepData > div').hide().filter('[data-step=' + indx + ']').show();
        }
      },
      {
        ButtonText: $form.data('installed'),
        beforeAction: function() {
          return true;
        },
        afterAction: function(indx) {
          $stepNumber.val(indx);
          $('.change__stepData > div').hide().filter('[data-step=' + indx + ']').show();
        }
      },
      {
        ButtonText: $form.data('done'),
        beforeAction: function() {
          return false;
        },
        afterAction: function(indx) {
          $stepNumber.val(indx);
          $('.change__stepData > div').hide().filter('[data-step=' + indx + ']').show();
          fillScale();
          FilterInit();
        }
      }
    ]
    ).init();

  promoStep.$buttonNext.click(function() {
    $form.submit();
  });

  $form.on('afterValidate', function(event, messages, errors) {
    if (errors.length > 0) {
      promoStep.enableButtons();
    }
  });

  $form.on('beforeSubmit', function() {
    var data = $form.serializeArray();
    data.push({name: 'submit', value: true});

    switch (promoStep.$activeStep) {
      case 1:
        $.ajax({
          url: $form.attr('action'),
          type: "POST",
          dataType: "json",
          data: data
        }).done(function(res) {
          $sourceIdInput.val(res.id);
          $('.hash').text(res.hash);
          var phpScript = $('.php-script'),
              url = $('#sourceform-url').val(),
              checkLink = $('.check'),
              // вырезаю теги из URL для предотвращения вставки js
              checkUrl = url.replace(/<\/?[^>]+>/gi, '') + '/' + checkLink.data('check');
          phpScript.html(phpScript.html().replace(/'(|[a-z0-9]+)'\)\;/gi, "'" + res.hash + "');"));
          checkLink.text(checkUrl).attr('href', checkUrl);
          promoStep.nextStep();
        });
        break;
      case 2:
        promoStep.nextStep();
        break;
      case 3:
        $.ajax({
          url: $form.attr('action'),
          type: "POST",
          dataType: "json",
          data: data
        }).done(function() {
          promoStep.nextStep();
        });
    }
    return false;
  });
});