$(function () {

  var init = function() {

    var from = $('#notificationform-datebegin'),
      to = $('#notificationform-dateend'),
      $pjaxContainer = $('#notify-list'),
      $form = $('#notification-form'),
      $filterLabel = $('.change_date-period > label')
      needUpdate = false,
      defaultUserInteractionTimerValue = 1,
      pjaxFormSubmitCounter = 2,
      userInteractionTimer = 1,
      updateTimer = setInterval(function() {
        if (needUpdate && userInteractionTimer == pjaxFormSubmitCounter) {
          sendForm();
          needUpdate = false;
          clearInterval(updateTimer);
        }
        if (needUpdate) userInteractionTimer += 1;
      }, 1000);

    $('#notification-form').on('change', 'input', function() {
      needUpdate = true;
      userInteractionTimer = defaultUserInteractionTimerValue;
    });

    var sendForm = function() {
      $('#notification-form').trigger('submit');
    };


    var dp_mobile_start = $('#m_notificationform-datebegin');
    var dp_mobile_end = $('#m_notificationform-dateend');

    setDpDate('notificationform-datebegin', true);
    setDpDate('notificationform-dateend', true);

    $(document).on('change.dp', '#dp_mobile input', function(e) {
      setDpDate(e.target.id, false);
    });

    $(".change_date-period input").change(function () {
      var start = $(this).data('start');
      var end = $(this).data('end');

      if(start !== undefined && end !== undefined) {
        $('.dp_container').hide();
        $("#notificationform-datebegin").kvDatepicker("setDate", start + ""),
        $("#notificationform-dateend").kvDatepicker("setDate", end + "");
      } else {
        $('.dp_container').show();
      }
    });

    $('.input-daterange input').on('change', function() {
      $filterLabel.removeClass('active').find('input').prop('checked', false);
      if (from.val() != undefined) {
        $('[data-from="' + from.val() + '"][data-to="' + to.val() + '"]').parent().addClass('active');
      } else {
        $('[data-to="' + to.val() + '"]').parent().addClass('active');
      }
    });


    $('.pagination li').on('click', function () {
      $('html, body').stop().animate({
        scrollTop: $('.bgf.news').offset().top
      }, 500);
    });

    var datepickerDropdown = $('.datepicker-dropdown');
    if (datepickerDropdown.is(':visible')) {
     var rangeStart = datepickerDropdown.find('.range-start');
     datepickerDropdown.remove();
      if (rangeStart.hasClass('active')) {
        from.trigger('click').trigger('focus');
      } else {
        to.trigger('click').trigger('focus');
      }
    }

    $pjaxContainer.on('pjax:click', function(event, settings) {
      settings.data = $form.serializeArray();
    });

  };



  $(document).on('pjax:end', init);
  init();
});