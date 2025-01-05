var $formStep3 = $('#linkStep3Form'),
  $linkIdInput = $formStep3.find('input[name="LinkStep3Form[id]"]'),
  $resultLink = $('#resultLink'),
  $subid1 = $('#linkstep3form-subid1'),
  $subid2 = $('#linkstep3form-subid2'),
  $erid = $('#linkstep3form-erid'),
  $adv_network = $('#linkstep3form-adv_network'),
  $adv_site_id = $('#linkstep3form-adv_site_id'),
  $adv_site_domain = $('#linkstep3form-adv_site_domain'),
  $cid = $('#linkstep3form-cid'),
  $cidValue = $('#linkstep3form-cid_value'),
  $dynamicTrafficBack = $('#trafficback_radio_2'),
  $allMarks = $('.all_input_mark input'),
  $postbackTestForm = $('#testPostbackUrl');

$formStep3.on('afterValidate', function(event, messages, errors) {
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

$formStep3.on('beforeSubmit', function() {
  var data = $formStep3.serializeArray();
  var step1Data = $('#linkStep1Form').serializeArray();
  var step2Data = $('#linkStep2Form').serializeArray();
  step1Data = step1Data.filter(function (item) {
    return item.name !== 'stepNumber';
  });
  step2Data = step2Data.filter(function (item) {
    return item.name !== 'stepNumber';
  });
  data = data.concat(step1Data, step2Data);
  data.push({name: 'submit', value: true});

  $.ajax({
    url: $formStep3.attr('action'),
    type: "POST",
    dataType: "json",
    data: data
  }).done(function(res) {
    $resultLink.html(res.link);
    window.promoStep.nextStep();
  }).fail(function() {
    window.promoStep.enableButtons();
  });

  return false;
});

$('.selectpicker').selectpicker();

if (typeof LINK_ID !== 'undefined') {
  $linkIdInput.val(LINK_ID);
}
if (typeof LINK_NAME !== 'undefined') {
  $resultLink.html(LINK_NAME);
}
updateIps();
updateLandStatuses();
//обновить показ revshare или cpa
//landing-has-revshare
//landing-has-cpa
if (typeof LINK_LANDINGS_HAS_REVSHARE !== 'undefined') {
  var landingsHasRevshare = JSON.parse(LINK_LANDINGS_HAS_REVSHARE) || false;
  landingsHasRevshare ? $('#landings-has-revshare').removeClass('hidden') : $('#landings-has-revshare').addClass('hidden');
}
if (typeof LINK_LANDINGS_HAS_CPA !== 'undefined') {
  var landingsHasCPA = JSON.parse(LINK_LANDINGS_HAS_CPA) || false;
  landingsHasCPA ? $('#landings-has-cpa').removeClass('hidden') : $('#landings-has-cpa').addClass('hidden');
}

new Clipboard('.copy-button');
new Clipboard('.copy');

var tm;
$(document).on('click', '.copy', function() {
  $(this).addClass('active').prev('pre').trigger('click');
  clearInterval(tm);
  tm = setTimeout(function() {
    $(this).removeClass('active');
  }.bind(this), 3000);
});

// Обновляем ссылку
function updateLink() {
  var cid = $cid.val().trim() ? $cid.val().trim() : 'cid';
  var params = [
    ($subid1.val() ? ('subid1=' + $subid1.val().replace(/\s/g, '') ) : ''),
    ($subid2.val() ? ('subid2=' + $subid2.val().replace(/\s/g, '') ) : ''),
    ($erid.val() ? ('erid=' + $erid.val().replace(/\s/g, '') ) : ''),
    ($adv_network.val() ? ('adv_network=' + $adv_network.val().replace(/\s/g, '') ) : ''),
    ($adv_site_id.val() ? ('adv_site_id=' + $adv_site_id.val().replace(/\s/g, '') ) : ''),
    ($adv_site_domain.val() ? ('adv_site_domain=' + $adv_site_domain.val().replace(/\s/g, '') ) : ''),
    ($cidValue.val() ? (cid + '=' + $cidValue.val().replace(/\s/g, '') ) : ''),
    ($dynamicTrafficBack.is(':checked') ? 'back_url=' : '')
  ].filter(function (val) { return val != '';}).join('&');

  $resultLink.text($resultLink.text().split('?')[0] + (params ? '?' + params : ''));
}

//Переключатель trafficback
$('.trafficback input').change(function() {
  $('.trafficback-tab .tab-pane').hide();
  $($(this).data('tab')).fadeIn(100);
  updateLink();
});

//Следим за метками
var marks = $('.input_mark input');
marks.eq(0).on('keyup', function() {
  if ($(this).val().length > 0) {
    marks.eq(1).prop('disabled', false);
  } else {
    $(this).val(marks.eq(1).val());
    marks.eq(1).prop('disabled', $(this).val().length === 0).val('');
    $(this).trigger('change');
  }
});
marks.eq(1).parent().on('click', function() {
  if ($(this).find('input').prop('disabled')) {
    marks.eq(0).focus();
  }
});
$allMarks.on('change', updateLink);

$('.ip_ranges').change(function() {
  updateIps();
});

function updateIps() {
  var format = $('input[name=ip_ranges_operators]:checked').val(),
    group = $('input[name=divide_by_operators]').is(":checked") ? 1 : 0,
    linkId = $linkIdInput.val();

  $.ajax({
    url: '/partners/links/ip-list/',
    type: "POST",
    data: {
      id: linkId,
      group: group,
      format: format
    }
  }).done(function(res) {
    $('.pre__list').html(res);
  });
}

function updateLandStatuses() {
  $.ajax({
    url: '/partners/links/landing-statuses/',
    type: "POST",
    data: {
      id: $linkIdInput.val()
    }
  }).done(function(res) {
    $(".link_copy-bottom").remove();
    $(res).insertAfter($(".result_url"));
  });
}

$('#postbackTest').on('click', function () {
  var $newForm = $formStep3.clone();
  // шаг 4 - тестирование Postback Url
  $newForm.find('input[name=stepNumber]').val(4);
  var data = $newForm.serializeArray();

  $.ajax({
    url: $formStep3.attr('action'),
    type: "POST",
    dataType: "json",
    data: data
  }).done(function(res) {
    $formStep3
      .find('.form-group')
      .removeClass('has-error')
      .find('.help-block').empty();
    $.each( res, function( field, error ){
      $formStep3
        .find('.field-' + field)
        .addClass('has-error')
        .find('.help-block').html(error);
    });
    if (res.length === 0) {
      $('#postbackTestModal').modal();
    }
  });

  return false;
});


// жуткий код который вроде обновляет скрытые поля во вьюхе (сделано через жопу),
// вынес просто в отдельное событие, чтобы можно было его отрубать
$postbackTestForm.on('submit.updateCheckboxes', function(e) {
  $('#testpostbackurlform-on').val($('#notifySubscribeCheck').prop('checked') ? 1 : 0);
  $('#testpostbackurlform-off').val($('#notifyUnsubscribeCheck').prop('checked') ? 1 : 0);
  $('#testpostbackurlform-rebill').val($('#notifyRebillCheck').prop('checked') ? 1 : 0);
  $('#testpostbackurlform-cpa').val($('#notifySellCheck').prop('checked') ? 1 : 0);
});

$postbackTestForm.on('submit', function(e) {
  e.preventDefault();

  $('#postbackTestResult').html('');

  $('#testpostbackurlform-postbackurl').val($('#linkstep3form-postback_url').val());
  $('#testpostbackurlform-linkid').val($('#linkId').val());

  var data = $postbackTestForm.serializeArray();

  $.ajax({
    url: $postbackTestForm.attr('action'),
    type: 'post',
    data: data
  }).done(function(res) {
    if(res.success) {
      $postbackTestForm
        .find('.form-group')
        .removeClass('has-error')
        .find('.help-block').empty();
      $('#postbackTestResult').html(res.data);
    } else {
      var errors = res.error;
      if(errors != null) {
        $.each( errors, function( field, error ){
          $postbackTestForm
            .find('.field-' + field)
            .addClass('has-error')
            .find('.help-block').html(error);
        });
      }
    }
  });
});

var glPb = $('#gl_pb'),
  pbUrl = $('#linkstep3form-postback_url'),
  tagsTable = $('#table'),
  pbTest = $('#postbackTest'),
  pbTags = $('#postback_tags'),
  pbUrlPrevColor = pbUrl.css('color'),
  tagsPrevColor = pbTags.find('span').css('color');

// обновляем состояние формы, при выборе галки "Использовать глобальный постбек"
function updateState() {
  if (glPb.is(':checked')) {
    pbUrl.prop('disabled', true);
    pbUrl.css('color', '#AAA');
    pbTags.find('span').css('color', '#AAA');
    pbTest.css('pointer-events', 'none');
    pbTags.css('pointer-events', 'none');
    tagsTable.collapse('hide');
  } else {
    pbUrl.prop('disabled', false);
    pbUrl.css('color', pbUrlPrevColor);
    pbTags.find('span').css('color', tagsPrevColor);
    pbTest.css('pointer-events', 'auto');
    pbTags.css('pointer-events', 'auto');
  }
}
updateState();
glPb.on('change', updateState);