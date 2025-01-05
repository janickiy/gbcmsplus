$('form#' + '{{id}}').on('beforeSubmit', function () {
  var form = this;
  var $form = $(this);
  var data = $form.serializeArray();
  var yiiFormData = $form.data('yiiActiveForm');
  var $button = yiiFormData.submitObject;
  if ($button && $button.length && $button.attr('name')) {
    data.push({name: $button.attr('name'), value: $button.attr('value')});
  }

  data.push({name: 'submit', value: true});

  $.ajax({
    url: form.action,
    type: "POST",
    dataType: "json",
    data: data,
    success: '{{ajaxSuccess}}',
    error: '{{ajaxError}}',
    beforeSend: '{{ajaxBeforeSend}}',
    complete: '{{ajaxComplete}}'
  });
  return false;
}).on('beforeValidate', function (event, messages, deferreds) {
  var beforeFormBlock = $.Event('beforeFormBlock');
  $(this).trigger(beforeFormBlock, [messages, deferreds]);

  if (beforeFormBlock.result === false) return false;

  var $formBlocker = $("<div />", {id: "form-blocker-" + "{{id}}"})
      .css({"position": "absolute", "width": "100%", "height": "100%", "left": "0", "top": "0", "z-index": "1000"})
    ;
  var $form = $(this);
  var yiiFormData = $form.data('yiiActiveForm');
  var $button = yiiFormData.submitObject;
  if ($button) {
    $button
      .addClass("ladda-button")
      .attr("data-style", "slide-down")
      .ladda()
      .ladda("start")
    ;
  }

  var $modal = $formBlocker.closest(".modal");
  if ($modal.length > 0) {
    $modal.on("hide.bs.modal.prevent", function (e) {
      e.preventDefault();
    });
  }
}).on('afterValidate', function (event, messages, errorAttributes) {
  if (errorAttributes.length < 1) return;
  var $form = $(this);
  var yiiFormData = $form.data('yiiActiveForm');
  var $button = yiiFormData.submitObject;

  $(this)
    .css({"position": "static"});
  if ($button) {
    $button
      .ladda()
      .ladda("stop")
    ;
  }
  var $formBlocker = $("#form-blocker-" + '{{id}}'),
    $modal = $formBlocker.closest(".modal");
  $formBlocker.remove();
  if ($modal.length > 0) {
    $modal.off("hide.bs.modal.prevent");
  }
});