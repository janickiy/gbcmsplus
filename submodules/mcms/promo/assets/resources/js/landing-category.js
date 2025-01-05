$(function() {
  var $categorySelect
    , $categoryInput
    , $categorySortable;

  $(document).on('modal.landing_category.show', function() {
    $categorySelect = $('#alterCategorySelect');
    $categoryInput = $('#landingcategory-alter_categories');
    $categorySortable = $('#landingcategory-alter_categories-sortable');

    var selected = getSelected();
    selected.forEach(function(code) {
      $categorySelect.find('option[value="' + code + '"]').hide();
    });
  });

  $(document).on('click', '#alterCategoryAdd', function() {
    var $selectedOption = $categorySelect.find('option:selected');

    if ($categoryInput.val().split(',').indexOf($selectedOption.val()) == -1) {
      var code = $selectedOption.val();

      $('<li/>', {
        'aria-grabbed': 'false',
        'role': 'option',
        'data-key': code,
        'html': $selectedOption.text() + '<button type="button" class="close remove-category"><span>×</span></button>',
      }).appendTo($categorySortable).prop('draggable', true);

      $categorySelect.find('option[value="' + code + '"]').hide();

      var selected = getSelected();
      selected.push($selectedOption.val());
      $categoryInput.val(selected.join((',')));

      $categoryInput.change();

      $categorySortable.sortable('reload');
    }
  });

  $(document).on('click', '.remove-category', function() {
    var $li = $(this).closest('li')
      , code = $li.data('key');

    $li.remove();

    $categorySelect.find('option[value="' + code + '"]').show();

    var selected = getSelected();
    selected.splice(selected.indexOf(code), 1);
    $categoryInput.val(selected.join(','));

    $categoryInput.change();
  });

  function getSelected() {
    return $categoryInput.val() ? $categoryInput.val().split(',') : [];
  }

});