var $stepsWrap = $('.steps_wrap'),
  $formStep1 = $('#linkStep1Form'),
  $streamSelect = $('#linkstep1form-stream_id'),
  $isNewStream = $('#linkstep1form-isnewstream'),
  $newStreamName = $('#linkstep1form-streamname'),
  $newStreamForm = $('#newStreamForm'),
  $newStreamText = $('#stream-name'),
  $newStreamModal = $('#streamModal'),
  $parkingModal = $('#parkingModal'),
  $domainId = $('#linkstep1form-domain_id'),
  $parkedDomains = $domainId.find('.parked-domains-group'),
  $systemDomains = $domainId.find('.system-domains-group'),
  domainClassActive = $stepsWrap.data('domain-class-active'),
  domainClassBanned = $stepsWrap.data('domain-class-banned');

$formStep1.on('afterValidate', function(event, messages, errors) {
  if (errors.length > 0) {
    window.promoStep.enableButtons();

    var errorMessages = [];

    $.each(messages, function(index, attribute) {
      $.each(attribute, function(index, message) {
        if (!!message) {
          errorMessages.push(message);
        }
      });
    });

    notifyInit(null, errorMessages.join(', '), false);
  }
});

$formStep1.on('beforeSubmit', function() {
  window.promoStep.nextStep();
  return false;
});

$('.selectpicker').selectpicker();

// Добавление потока
$streamSelect.on('change', function() {
  $isNewStream.val($(this).val() == 0 ? 1 : 0);
  $formStep1.yiiActiveForm('updateAttribute', 'linkstep1form-streamname', '');
});

$newStreamForm.on('beforeSubmit', function(event) {
  $newStreamText.closest('.form-group').removeClass('has-error').find('.help-block').addClass('hidden');
  $newStreamModal.modal('hide');

  var newStreamOption = $streamSelect.find('option.newStream');
  if (newStreamOption.length === 0) {
    newStreamOption = $('<option />', {
      value: 0,
      text: $newStreamText.val(),
      class: 'newStream'
    }).appendTo($streamSelect);
  } else {
    newStreamOption.text($newStreamText.val());
  }

  newStreamOption.siblings().prop('selected', false);
  newStreamOption.prop('selected', true);
  $streamSelect.val(0);
  $newStreamName.val($newStreamText.val());
  $isNewStream.val(1);

  $streamSelect.selectpicker('refresh');
  $formStep1.yiiActiveForm('updateAttribute', 'linkstep1form-streamname', '');
  return false;
});

function addDomain(id, url, active, isSystem) {
  $('<option>', {
    value: id,
    'data-content': '<i class=\'icon ' + (active ? domainClassActive : domainClassBanned) + ' icon-shield\'></i>' + url
  }).html(url).appendTo(isSystem ? $systemDomains : $parkedDomains);
}

$(document).on('mcms.domains.added', function(event, response) {
  $parkingModal.modal('hide');

  if (response.success) {
    addDomain(response.data.id, response.data.url, true, false);
    $domainId.val(response.data.id);
    $domainId.selectpicker('refresh');
  }
});