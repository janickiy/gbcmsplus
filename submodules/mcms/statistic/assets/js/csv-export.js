(function () {
  // TRICKY заменяем старую ссылку, на новую, чтобы сбросить все события
  var $elem = $('.export-full-csv');
  var $cln = $elem.clone();
  $cln.insertAfter('.export-full-csv');
  $elem.hide();

  var exportType = $('input[name="details_stat_export_type"]').val();
  // вешаем захардкоженый экшен экспорта
  $cln.on('click', function () {
    yii.confirm($(this).data('confirmMsg'), function () {
      var attrs = [];
      var $statisticPjax = $('#statistic-pjax');
      if ($statisticPjax.length) {
        $statisticPjax.find('table').find('th').each(function () {
          if ($(this).data('code') !== undefined && !$(this).hasClass('hidden')) {
            attrs.push($(this).data('code'));
          }
        });
        window.open('/admin/statistic/detail/download-csv/?export_type=' + exportType + '&url=' + encodeURIComponent(location.href) + '&attrs=' + attrs.join(','));
      }
    })
  });
})();