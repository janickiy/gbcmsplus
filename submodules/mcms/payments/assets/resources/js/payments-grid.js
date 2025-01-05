$(function() {
  $(document).on('mcms.payments.export.modal', function() {
    var $modal = $('#modalWidget'),
      $exportForm = $('#payments-export-form', $modal),
      $noPaymentsMessage = $('#no-payments-message', $modal),
      $exportLink = $('#export-link', $modal);

    $exportForm.on('change', 'input, select', function() {
      $noPaymentsMessage.addClass('hidden');
    });

    // Проверка на наличие выплат
    $exportForm.on('beforeSubmit', function() {
    });

    // Получение ссылки на архив
    $exportForm.on('getExportLink', function(event, res) {
      if (res.success === true) {
        $exportLink.removeClass('hidden').find('a').attr('href', res.data.link).text($exportForm.data('link-text'));
        $noPaymentsMessage.addClass('hidden');
      } else {
        $noPaymentsMessage.removeClass('hidden');
      }
    });

  });
});