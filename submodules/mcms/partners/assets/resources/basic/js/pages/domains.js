$(function() {
  var $parkingModal = $('#parkingModal');

  $(document).on('mcms.domains.added', function() {
    $parkingModal.modal('hide');
    $.pjax.reload('#domainsPjaxContainer');
  });
});