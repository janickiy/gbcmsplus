$(function() {
  var select2Options = {
    allowClear: true,
    theme: 'smartadmin',
    placeholder: '',
    ajax: {
      dataType: 'json',
      data: function(params) {
        var operatorId = $(this).closest('.item').find('.operators-selectpicker').val();

        return {
          operatorRequired: true,
          q: params.term ? params.term : '',
          operators: operatorId ? [operatorId] : [],
        };
      },
    }
  }

  $('.dynamicform_wrapper').on('initSelect2ForDynamicForm', '.select2', function() {
    var $this = $(this);

    select2Options.placeholder = $this.data('placeholder');
    select2Options.ajax.url = $this.data('url');
    $this.select2(select2Options);
  });

  // Включаем селект2 для уже существующих селектов на форме
  $('.select2').trigger('initSelect2ForDynamicForm');

  // Включаем селект2 при добавлении лендинга
  $('.dynamicform_wrapper').on('afterInsert', function(event, item) {
    $('.selectpicker', item).selectpicker();
    $('.select2', item).empty().trigger('initSelect2ForDynamicForm');
  });

  // Сбрасываем лендинг при смене оператора
  $('.dynamicform_wrapper').on('change', '.selectpicker', function(event) {
    $(event.target).closest('.item').find('.landings-select').val('').change();
  });

  $('#source-status').on('change', function(){
    var $rejectReasonWrap = $('#arbitrary-source-reject-reason');
    if (parseInt($(this).val()) !== $rejectReasonWrap.data('status-declined')) {
      $rejectReasonWrap.addClass('hide');
    } else {
      $rejectReasonWrap.removeClass('hide');
    }
  });

  var $postbackInput = $('[id*="postback_url"]');
  $('#gl_pb').on('change', function () {
    if ($(this).is(':checked')) {
      $postbackInput.prop('disabled', true);
    } else {
      $postbackInput.prop('disabled', false);
    }
  });
});