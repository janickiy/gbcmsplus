$(function () {
  var $statusInput = $('#userform-status'),
    $statusReasonWrap = $('#status-reason'),
    statusActive = $statusReasonWrap.attr('data-active-status'),
    currentStatus = $statusReasonWrap.attr('data-current-status'),
    $moderationReasonInput = $('#userform-moderationreason')
  ;

  $statusInput.on('change', function() {
    var value = $(this).val();
    if (statusActive == value || currentStatus == value) {
      $statusReasonWrap.addClass('hide');
      $moderationReasonInput.val('');
    } else {
      $statusReasonWrap.removeClass('hide');
    }
  })
});