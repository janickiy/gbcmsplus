(function ($) {
  $.fn.mainCurrenciesWidget = function () {
    var $container = $(this);
    // Если btn-group
    $container.on('click', 'button', function (e) {
      $(this).closest('.main_currencies-widget').trigger('mainCurrencyChange', [
        $(this).data('currency-code'),
        function () {
          this.siblings().removeClass('active');
          this.addClass('active');
        }.bind($(this))
      ]);
    });
    // Если селектпикер
    $container.on('changed.bs.select', function () {
      $(this).trigger('mainCurrencyChange', $(this).val());
    });

    // Обработчик клика по-умолчанию (отправка на сервер валюты для сохранения в куках)
    $container.on('mainCurrencyChange', function (event, newValue, callback) {
      if (event.isDefaultPrevented()) {
        return;
      }
      if ($.isFunction(callback)) {
        callback();
      }
      var $container = $(this);
      var cookieUrl = $container.data('url-cookie-set');
      var callbackFunction = $container.data('callback');
      $.get(cookieUrl, {'currencyCode': newValue}, function () {
        $container.trigger('mainCurrencyChanged', newValue);
        eval(callbackFunction);
      });
    });
  };
})(window.jQuery);
