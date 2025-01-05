if (!window.multilangInputBinded) {
  window.multilangInputBinded = true;
  $(document).on('click', '.multilang-input .dropdown-menu > li > a', function () {
    $btn = $(this);
    $container = $(this).closest('.input-group');
    $inputs = $container.find('div[data-lang], textarea[data-lang]');
    $toggleBtn = $container.find('.dropdown-toggle');

    jQuery.each($inputs, function ($i, input) {
      $input = $(input);
      $input.toggleClass('hidden', $btn.data('lang') != $input.data('lang'));
      $toggleBtn.html($btn.data('lang').toUpperCase() + ' <span class="caret"></span>');
    });
  });
}