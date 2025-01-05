$(function () {

  var $delegateSelect = $('[name="delegated_to"]')
    , $delegateButton = $('#delegate-button')
    , delegatedTo = $delegateSelect.data('delegated-to')
    , delegateUrl = $delegateButton.attr('href')
    ;

  $delegateSelect.on('change', function (e) {
    e.target.value == delegatedTo || e.target.value == ''
      ? $delegateButton.hide()
      : $delegateButton.show()
    ;
  });

  $delegateSelect.trigger('change');

  $delegateButton.on('click', function (event) {
    event.preventDefault();
    $.ajax({
      url: delegateUrl,
      method: 'POST',
      data: {
        userId: $delegateSelect.find(':selected').attr('value')
      }
    }).done(function () {
      location.reload();
    });
  });
});
