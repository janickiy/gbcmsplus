$(document).on('afterValidate', 'form', function (evt, messages, attribute) {
  if (attribute.length == 0) return true;
  var tabId = $(attribute[0].input).closest('.tab-pane').attr('id');
  $('.nav-tabs a[href="#' + tabId + '"]').tab('show');
});