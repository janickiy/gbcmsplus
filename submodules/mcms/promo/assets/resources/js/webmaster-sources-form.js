$(function () {
  $('#source-status').on('change', function(){
    var $rejectReasonWrap = $('#webmaster-source-reject-reason');
    if ($(this).val() != $rejectReasonWrap.data('status-declined')) {
      $rejectReasonWrap.addClass('hide');
    } else {
      $rejectReasonWrap.removeClass('hide');
    }
  });
});