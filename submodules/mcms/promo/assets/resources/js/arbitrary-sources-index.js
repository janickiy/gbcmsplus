$(function () {
  $('body').tooltip({selector: '[data-toggle="tooltip"]'});
  $('#arbitrary-sources-pjax').on('click', '.source_operator_pagination_btn a', function(event){
    var href = $(this).attr('href');
    var pjaxContainer = $(this).closest('.pjax_source_operator_gridview');
    var key = pjaxContainer.data('key');
    $.pjax({
      type: 'POST',
      url: href,
      container: '#' + pjaxContainer.attr('id'),
      data: {expandRowKey: key},
      push: false,
      replace: false,
      scrollTo: false
    });
    event.preventDefault();
  });
});