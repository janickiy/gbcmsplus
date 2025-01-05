var isMobile = 767;
var isTablet = 991;
var isSmallDesktop = 1400;

function getMatchMedia(screen) {
  return window.matchMedia("(max-width: " + screen + "px)").matches;
}

var fontSize = getMatchMedia(isMobile) ? '8' : '12';

$(window).on('resize', function (event) {
  fontSize = getMatchMedia(isMobile) ? '8' : '12';
});

var defaultLegendClickHandler = Chart.defaults.global.legend.onClick;
var defaultTooltipTitleHandler = Chart.defaults.global.tooltips.callbacks.title;

var legend_config = function (position) {
  return {
    position: position,
    labels: {
      fontSize: 12,
      boxWidth : 12,
      padding : 16,
      fontStyle: 300
    }
  }
};

var tooltip_config = function (mode) {
  return {
    position: 'average',
    intersect: true,
    mode: mode,
    yPadding : 15,
    xPadding : 20,
    bodySpacing : 10,
    titleMarginBottom : 10,
    bodyFontStyle : 300,
    callbacks : {
      label : function(tooltipItem, data) {
        var currencySymbol = !!data.currencySymbol ? data.currencySymbol : '';
        var label = data.datasets[tooltipItem.datasetIndex].label || data.labels[tooltipItem.index];
        if (typeof data.caption !== 'undefined') {
          label = data.caption;
        }
        var value = (typeof tooltipItem.yLabel === 'undefined' || tooltipItem.yLabel === '') ? data.datasets[0].data[tooltipItem.index] : tooltipItem.yLabel;
        if (currencySymbol != '') {
          value = parseFloat(value).toFixed(2);
        }
        return '  ' + label + ': ' + value.toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1' + ' ') +  ' ' + currencySymbol;
      },
      title: function (tooltipItem, data) {
        return defaultTooltipTitleHandler.call(this, tooltipItem, data);
      }
    }
  }
};

function selectpickerInit(selector) {
  if( /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ) {
    $(selector).selectpicker('mobile');
  } else {
    $(selector).selectpicker({
      tickIcon: 'icon-checked'
    });
  }
}

$(function () {
  selectpickerInit('.selectpicker');
});

var dashboardCountries = $('#dashboard-countries');
var dashboardPeriodInput = $('#dashboard-periods').find('input[name="dashboard-period"]');
var dashboardForecast = $('#dashboard-forecast');
var dashboardCurrencies = $('#dashboard-currencies').find('input[name="dashboard-currency"]');
var investorCurrencyInput = $('#investors-currency').find('input[name="investor-currency"]');
var publisherTypeInput = $('#publishers-type').find('input[name="publisher-type"]');
var countryTypeInput = $('#countries-type').find('input[name="countries-type"]');

var dashboardFilters = function() {
  this.filters = {
    countries: null,
    period: null,
    currency: null,
    forecast: null,
    investorCurrency: null,
    publisherType: null,
    countryType: null
  };
  
  this.initCountries = function() {
    if (!this.filters.countries) {
      return;
    }
    
    dashboardCountries.val(this.filters.countries);
    dashboardCountries.selectpicker('refresh');
  };
  
  this.initPeriod = function() {
    if (!this.filters.period) {
      return;
    }
    
    dashboardPeriodInput.parent().removeClass('active');
    dashboardPeriodInput
      .filter('[value="' + this.filters.period + '"]')
      .prop('checked', true)
      .parent()
      .addClass('active');
  };
  
  this.initForecast = function() {
    if (this.filters.forecast === null) {
      return;
    }
    
    dashboardForecast.prop('checked', this.filters.forecast);
  };
  
  this.initCurrency = function() {
    if (!this.filters.currency) {
      return;
    }
    
    dashboardCurrencies.parent().removeClass('active');
    dashboardCurrencies
      .filter('[value="' + this.filters.currency + '"]')
      .prop('checked', true)
      .parent()
      .addClass('active');
  };
  
  this.initInvestorCurrency = function () {
    if (!this.filters.investorCurrency) {
      return;
    }

    investorCurrencyInput.parent().removeClass('active');
    investorCurrencyInput
      .filter('[value="' + this.filters.investorCurrency + '"]')
      .prop('checked', true)
      .parent()
      .addClass('active');
  };
  
  this.initPublisherType = function () {
    if (!this.filters.publisherType) {
      return;
    }
  
    publisherTypeInput.parent().removeClass('active');
    publisherTypeInput
      .filter('[value="' + this.filters.publisherType + '"]')
      .prop('checked', true)
      .parent()
      .addClass('active');
  };
  
  this.initCountryType = function () {
    if (!this.filters.countryType) {
      return;
    }
  
    countryTypeInput.parent().removeClass('active');
    countryTypeInput
      .filter('[value="' + this.filters.countryType + '"]')
      .prop('checked', true)
      .parent()
      .addClass('active');
  };
};
dashboardFilters.prototype.init = function (config) {
  this.filters = $.extend(this.filters, config);
  this.initCountries();
  this.initPeriod();
  // this.initCurrency();
  this.initForecast();
  this.initInvestorCurrency();
  this.initPublisherType();
  this.initCountryType();
};

window.dashboardFilters = new dashboardFilters();

(function($, global) {
  /**
   * Объект для формирования и подготовки данных к отправке на сервер
   * @param params
   * @constructor
   */
  var DashboardRequestObject = function(params) {
    this.prefix = '';
    this.filters = {};
    this.widgets = {};
    this.gadgets = {};
    this.events = [];

    this.configure(params);
  };

  /**
   * Заполняет внутренние свойства параметрами из конфига
   * @param params
   */
  DashboardRequestObject.prototype.configure = function (params) {
    if (params.hasOwnProperty('prefix')) {
      this['prefix'] = params.prefix;
    }
    if (params.hasOwnProperty('filters')) {
      this['filters'] = params.filters;
    }
    if (params.hasOwnProperty('widgets')) {
      this['widgets'] = params.widgets;
    }
    if (params.hasOwnProperty('gadgets')) {
      this['gadgets']= params.gadgets;
    }
    if (params.hasOwnProperty('events')) {
      this['events']= params.events;
    }
  };

  /**
   * Возвращает данные в необходимом для сервера виде
   * @returns {{}}
   */
  DashboardRequestObject.prototype.serialize = function () {
    var result = {};
    result[this.prefix + 'filters'] = this.filters;
    result[this.prefix + 'widgets'] = this.widgets;
    result[this.prefix + 'gadgets'] = this.gadgets;

    return result;
  };

  /**
   * Содержит наборы виджетов, гаджетов и событий.
   * Отслеживает события и отправляет данные на сервер
   * @constructor
   */
  var DashboardRequest = function () {
    this.prefix = '';

    /**
     * Список проинициализированных виджетов
     * @type {{name: {name: String, success: Function, events: Array}, ...}}
     */
    this.widgets = {};

    /**
     * Список проинициализированных гаджетов
     * @type {{name: {name: String, success: Function, events: Array}, ...}}
     */
    this.gadgets = {};

    /**
     * Содержит список виджетов и гаджетов, которые надо обновлять при определенных событиях
     * @type {{widgets: {dashboard:filter: Array, dashboard:forecast: Array}, gadgets: {dashboard:filter: Array}}}
     */
    this.events = {
      widgets: {
        'dashboard:filter': [],
        'dashboard:forecast': []
      },
      gadgets: {
        'dashboard:filter': []
      }
    };

    /**
     * Url для отправки данных дашборда на сервер
     * @type {string}
     */
    this.url = '';

    var self = this;

    /**
     * Подготовить и отправить данные на сервер при событии dashboard:filter
     */
    $(document).on('dashboard:filter', function (e) {
      var object = self.getObjectByEvent('dashboard:filter');
      self.send(object);
    });

    /**
     * Подготовить и отправить данные на сервер при событии dashboard:forecast
     */
    $(document).on('dashboard:forecast', function (e) {
      var object = self.getObjectByEvent('dashboard:forecast');
      self.send(object);
    });
  };

  /**
   * Добавить виджет в общий список и подготовить события, на которых он будет обновлен
   * @param widget
   */
  DashboardRequest.prototype.addWidget = function (widget) {
    var self = this;
    if (widget.hasOwnProperty('events')) {
      widget.events.forEach(function (eventName) {
        self.events.widgets[eventName][self.events.widgets[eventName].length] = widget.name;
      });
    }
    this.widgets[widget.name] = widget;
  };

  /**
   * Добавить гаджет в общий список и подготовить события, на которых он будет обновлен
   * @param gadget
   */
  DashboardRequest.prototype.addGadget = function (gadget) {
    var self = this;
    if (gadget.hasOwnProperty('events')) {
      gadget.events.forEach(function (eventName) {
        self.events.gadgets[eventName][self.events.gadgets[eventName].length] = gadget.name;
      });
    }
    this.gadgets[gadget.name] = gadget;
  };

  /**
   * @param prefix
   */
  DashboardRequest.prototype.setPrefix = function (prefix) {
    this.prefix = prefix;
  };

  /**
   * @param url
   */
  DashboardRequest.prototype.setUrl = function (url) {
    this.url = url;
  };

  /**
   * Метод для получения готового объекта DashboardRequestObject
   * @param params
   * @returns {DashboardRequestObject}
   */
  DashboardRequest.prototype.getObject = function (params) {
    if (!params) {
      params = {};
    }

    var config = {
      prefix: this.prefix,
      filters: this.getFilters()
    };

    if (params.hasOwnProperty('widgets')) {
      config['widgets'] = params.widgets;
    }
    if (params.hasOwnProperty('gadgets')) {
      config['gadgets'] = params.gadgets;
    }

    return new DashboardRequestObject(config);
  };

  /**
   * Метод для получения текущих фильтров
   * @returns {{countries: *, period, forecast: *}}
   */
  DashboardRequest.prototype.getFilters = function () {
    return {
      countries: dashboardCountries.val(),
      period: dashboardPeriodInput.filter(':checked').val(),
      forecast: dashboardForecast.is(':checked')
    }
  };

  /**
   * Метод для получения готового объекта DashboardRequestObject,
   * но с элемементами, предназначенными для текущего события
   * @param eventName
   * @returns {DashboardRequestObject}
   */
  DashboardRequest.prototype.getObjectByEvent = function(eventName) {
    var params = {
      widgets: {},
      gadgets: {}
    };
    var self = this;
    this.events.widgets.hasOwnProperty(eventName) && this.events.widgets[eventName].forEach(function (name) {
      params['widgets'][name] = {
        name: self.widgets[name].name
      };
      // Для изменяющихся фильтров передаем селектор, чтобы получить актуальные данные
      if (self.widgets[name].hasOwnProperty('filterSelector') && self.widgets[name].filterSelector) {
        params['widgets'][name]['filter'] = $(self.widgets[name].filterSelector).val();
      }

    });
    this.events.gadgets.hasOwnProperty(eventName) && this.events.gadgets[eventName].forEach(function (name) {
      params['gadgets'][name] = {
        name: self.gadgets[name].name
      };
    });

    return this.getObject(params);
  };

  /**
   * Отправить запрос на сервер и обработать ответ
   * @param object
   */
  DashboardRequest.prototype.send = function(object) {
    if (!object) {
      object = this.getObject();
    }
    var self = this;

    $.ajax({
      type: 'POST',
      url: this.url,
      data: object.serialize(),
      success: function (data) {
        if (data.hasOwnProperty('widgets') && data.widgets) {
          $.each(data.widgets, function () {
            if (self.widgets.hasOwnProperty(this.name)) {
              /** Если виджет проинициализирован, выполнить callback */
              self.widgets[this.name].success(this.data);
            }
          });
        }
        if (data.hasOwnProperty('gadgets') && data.gadgets) {
          $.each(data.gadgets, function () {
            if (self.gadgets.hasOwnProperty(this.name)) {
              /** Если гаджет проинициализирован, выполнить callback */
              self.gadgets[this.name].success(this.data);
            }
          });
        }
      }
    });
  };

  global.DashboardRequest = global.DashboardRequest || new DashboardRequest;
})(jQuery, window);

dashboardCountries.on('hidden.bs.select', function () {
  $(document).trigger('dashboard:filter');
});

dashboardPeriodInput.on('change', function () {
  $(document).trigger('dashboard:filter');
});

dashboardForecast.on('change', function () {
  $(document).trigger('dashboard:forecast');
});

// dashboardCurrencies.on('change', function () {
//   $(document).trigger('dashboard:filter', {
//     countries: dashboardCountries.val(),
//     period: dashboardPeriodInput.filter(':checked').val(),
//     forecast: dashboardForecast.is(':checked'),
//     currency: $(this).val()
//   });
//   Cookies.set(currencyFilterCookieKey, $(this).val(), {expires: 1});
// });

// После рендеринга плагина убирает неразрынвый пробел у кнопки раскрытия списка
$('#dashboard-items-select').on('rendered.bs.select', function () {
  var $dropdownButton = $(this).siblings('button[data-id="' + $(this).attr('id') + '"]');
  $dropdownButton.html($dropdownButton.html().replace(/&nbsp;/g, ''));
});

window.addEventListener('load', function () {
  $(document).trigger('dashboard:filter');
});

/* TOOLTIP */
$(".overview__item_value .gadget-value").mouseenter(function() {
  $(this).parent().attr('data-state','1');
  $(this).siblings('.overview__tooltip').css("display", "block");
}).mouseleave(function() {
  $(this).parent().attr('data-state','0');
  $(this).siblings('.overview__tooltip').css("display", "none");
});