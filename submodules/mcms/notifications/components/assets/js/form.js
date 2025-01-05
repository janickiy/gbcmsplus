$(function() {
  var $checkboxes = $('#roles_checkbox_list input');
  var checkboxes = {};

  $checkboxes.eq(0).on('change', function() {

    var isOwnerChecked = $(this).prop('checked');

    $checkboxes.slice(1)
      .prop('disabled', $(this).prop(':checked'))
      .each(function() {
        var key = $(this).prop('value');

        if (isOwnerChecked) {
          checkboxes[key] = $(this).prop('checked');
          $(this).prop('checked', false);
        } else {
          if (checkboxes.hasOwnProperty(key) && checkboxes[key]) {
            $(this).prop('checked', true);
          }
          checkboxes[key] = false;
        }
      })

      .closest('.checkbox').attr('disabled', function(index, attr){
        return !attr;
    })
    ;
  });

  if ($('input[name=use_owner]').val() == '1') {
    $checkboxes.eq(0)
      .prop('checked', true)
      .trigger('change')
    ;
  }
});