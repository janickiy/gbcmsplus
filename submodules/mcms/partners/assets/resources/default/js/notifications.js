$(function () {

  var init = function() {

    var from = $('#notificationform-datebegin'),
        to = $('#notificationform-dateend'),
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

    $('.set-date').click(function (e) {
      e.preventDefault();
      from.val($(this).data('from')).trigger('change');
      to.val($(this).data('to')).trigger('change');
    });

    $('#notification-form').on('change', 'input', function() {
      needUpdate = true;
      userInteractionTimer = defaultUserInteractionTimerValue;
    });

    var sendForm = function() {
      $('#notification-form').trigger('submit');
    };
  };

  $(document).on('pjax:end', init);
  init();
});