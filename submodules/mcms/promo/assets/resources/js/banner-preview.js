$(function() {
  var $form = $('form')
    , $saveButton = $('#save', $form)
    , $previewButton = $('.preview', $form)
    , defaultActionUrl = $form.attr('action')
    ;

  if ($saveButton.length != 0) {
    $saveButton.on('click', function(){
      $form
        .attr('action', defaultActionUrl)
        .removeAttr('target')
      ;
    });
  }

  if ($previewButton.length != 0) {
    $previewButton.on('click', function(e) {
      e.preventDefault();
      $form
        .attr('action', $(this).attr('formaction'))
        .attr('target', '_blank')
        .trigger('submit')
      ;
    });
  }
});