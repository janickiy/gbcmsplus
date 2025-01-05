/**
 * Сравнение массивов
 * @param {array} arr Первый массив
 * @param {array} arr2 Второй массив
 * @return {bool}
 */
function compareArrays(arr, arr2) {
  if (arr == null) arr = [];
  if (arr2 == null) arr2 = [];
  if(arr.length != arr2.length) return false
  var on = 0;
  for( var i = 0; i < arr.length; i++ ){
    for( var j = 0; j < arr2.length; j++ ){
      if(arr[i] === arr2[j]){
        on++
        break
      }
    }
  }
  return on==arr.length?true:false
}

// @see class Alert
// @see notifyInit() в партнерке
function notifyInit(title, message, success) {
  var color = success ? 'rgb(115, 158, 115)' : 'rgb(196, 106, 105)';
  var icon = success ? 'fa fa-exclamation-triangle fadeInLeft animated' : 'miniPic fa fa-warning shake animated';
  var config = {
    "content": message,
    "color": color,
    "iconSmall": icon,
    "timeout" : 4000,
    "sound": false
  };
  if (title) config.title = title;

  $.smallBox(config);
}

var rgk = {
  math: {
    round: function (sum) {
      return this._decimalOperation(sum, 2, 'round');
    },

    floor: function (sum) {
      return this._decimalOperation(sum, 2, 'floor');
    },

    ceil: function (sum) {
      return this._decimalOperation(sum, 2, 'ceil');
    },

    // https://habrahabr.ru/post/312880/#s2_3
    _decimalOperation: function (value, decimals, operation) {
      return Number(Math[operation](value + 'e' + decimals) + 'e-' + decimals);
    }
  },

  formatter: {
    asCurrency: function (amount, currency) {
      amount = this.asDecimal(amount);

      if (currency) {
        amount = amount + ' ' + this.getCurrencyIcon(currency);
      }

      return amount;
    },

    asDecimal: function (value, decimals) {
      decimals = decimals || 2;
      // Округление и форматирование
      value = rgk.math.round(parseFloat(value))
        .toFixed(decimals)
        .toString()
        .replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1' + ' ');

      return value;
    },

    getCurrencyIcon: function (currency) {
      switch (currency) {
        case 'rub':
          return '<i class="fa fa-rub"></i>';
          break;
        case 'usd':
          return '<i class="fa fa-usd"></i>';
          break;
        case 'eur':
          return '<i class="fa fa-eur"></i>';
          break;
        default:
          return currency;
      }
    }
  }
};

// После завершения каждого аякс запроса
$(document).on('ajaxComplete', function (event, xhr, settings) {
  // Если пришел ответ 403, показываем флешку с ошибкой
  if (xhr.status == 403) {
    $.smallBox({
      "color": "rgb(196, 106, 105)",
      "timeout" : 4000,
      "title": xhr.responseText,
      "sound": false,
      "iconSmall": "miniPic fa fa-warning shake animated"
    });
  }
  // Проверяем, есть ли открытые select2
  var isOpenedSelect2 = false;
  $('select').filter(function () {
    return !!$(this).data('select2');
  }).each(function () {
    if ($(this).select2('isOpen')) {
      isOpenedSelect2 = true;
      return false;
    }
  });
  // Если нет, но есть select2 dropdown, то удаляем его
  var $select2Dropdown = $('.select2-dropdown');
  if (!isOpenedSelect2 && $select2Dropdown.length > 0) {
    $select2Dropdown.parent().remove();
  }

  // Проверяем, если ли открытые datepicker
  var isOpenedDatepicker = false;
  $('input').filter(function () {
    return !!$(this).data('datepicker');
  }).each(function () {
    if ($(this).data('datepicker').picker.is(':visible') === true) {
      isOpenedDatepicker = true;
      return false;
    }
  });
  // Если нет, но есть datepicker dropdown, то удаляем его
  var $datepickerDropdown = $('.datepicker');
  if (!isOpenedDatepicker && $datepickerDropdown.length > 0) {
    $datepickerDropdown.remove();
  }
});

$('#left-panel nav>ul>li').on('mouseenter', function () {
  if ($('body').hasClass('minified')) {
    var $absoluteUl = $(this).find('ul:first');
    var $mainWrapper = $('.main-wrapper');
    if ($absoluteUl.length > 0) {
      var absoluteUlHeightOffset = $absoluteUl.outerHeight() + $absoluteUl.offset().top;
      var mainWrapperHeightOffset = $mainWrapper.height() + $mainWrapper.offset().top;
      if (absoluteUlHeightOffset > mainWrapperHeightOffset) {
        $mainWrapper.height($mainWrapper.height() + absoluteUlHeightOffset - mainWrapperHeightOffset);
      }
    }
  }
}).on('mouseleave', function () {
  if ($('body').hasClass('minified')) {
    var $absoluteUl = $(this).find('ul:first');
    var $mainWrapper = $('.main-wrapper');
    if ($absoluteUl.length > 0) {
      var absoluteUlHeightOffset = $absoluteUl.outerHeight() + $absoluteUl.offset().top;
      var mainWrapperHeightOffset = $mainWrapper.height() + $mainWrapper.offset().top;
      if (absoluteUlHeightOffset < mainWrapperHeightOffset) {
        $mainWrapper.height('auto');
      }
    }
  }
});

var minifiedMenuKey = 'minifiedMenu';
var hiddenMenuKey = 'hiddenMenu';

$(document).on('click', '[data-action="minifyMenu"]', function (e) {
  if ($.root_.hasClass('minified')) {
    Cookies.set(minifiedMenuKey, 'true', { expires: 7 });
  } else {
    Cookies.set(minifiedMenuKey, 'false');
  }
});
$(document).on('click', '[data-action="toggleMenu"]', function (e) {
  if ($.root_.hasClass('hidden-menu')) {
    Cookies.set(hiddenMenuKey, 'true', { expires: 7 });
    Cookies.set(minifiedMenuKey, 'false');
  } else {
    Cookies.set(hiddenMenuKey, 'false');
  }
});

initApp.mobileCheckActivation();