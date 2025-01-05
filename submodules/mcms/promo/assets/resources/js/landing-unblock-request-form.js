$(function() {
  $('#landingunblockrequest-status').on('change', function(){
    var $rejectReasonWrap = $('#landing-unblock-request-reject-reason');
    if (parseInt($(this).val()) !== $rejectReasonWrap.data('status-disabled')) {
      $rejectReasonWrap.addClass('hide');
    } else {
      $rejectReasonWrap.removeClass('hide');
    }
  });
});