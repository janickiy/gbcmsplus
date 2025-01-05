$(function () {
  // Включаем селект2 при добавлении лендинга
  $('.dynamicform_wrapper').on('afterInsert', function (event, item) {
    $(item).find('select').each(function () {
      $(this).find('option:selected').removeAttr("selected");
    });
    $('.selectpicker', item).selectpicker();
    $(item).find('.delete-button').attr('data-id', '').click(function (e) {
      e.preventDefault();

      var $button = $(e.target);

      yii.confirm($button.attr('data-confirm-text'), function () {
        $button.closest('.item').remove();
      });
    });
  });
});