// Ключи для хранения шаблонов и выбранных столбцов в куки привязаны к роли,
// что бы после авторизации под другим пользователем не было пустой таблицы из-за отсутствия прав на столбцы и других конфликтов
$(function () {
  var $from = $("[name='FormModel[dateFrom]']"),
      $to = $("[name='FormModel[dateTo]']"),
      $statisticPeriod = $('#statistic-period'),
      $filterButton = $('.filter-button'),
      $statisticDataTable = $('#statisticTable'),
      $tableFilterSelect = $('#table-filter'),
      $filterForm = $('#statistic-filter-form'),
      $hiddenFilters = $('#hidden-filters'),
      $isAutoApply = $('#is_auto_apply'),
      pjaxSelector = '#statistic-pjax',
      needUpdate = false,
      defaultUserInteractionTimerValue = 1,
      pjaxFormSubmitCounter = 2,
      userInteractionTimer = 1,
      updateTimer = setInterval(function () {
        if (needUpdate && userInteractionTimer == pjaxFormSubmitCounter) {
          $filterForm.trigger('submit');
        }
        if (needUpdate) {
          userInteractionTimer += 1;
        }
      }, 500),
      $tableHideColumnHandler = function(){},
      table = undefined,
      columnsCookieKey = rgkUser.role + '_statistic_column_' + window.location.pathname,
      filterCookieKey = rgkUser.role + '_statistic_showFilter',
      filterAutoApplyCookieKey = 'filterAutoApplyCookieKey',
      formAutoSubmitTimer = undefined
      ;

  if (Cookies.get(filterAutoApplyCookieKey) === 'true') {
    $isAutoApply.prop('checked', true);
  }
  $isAutoApply.on('change', function(){
    Cookies.set(filterAutoApplyCookieKey, $isAutoApply.is(":checked"));
  });

  $(document).on('event_filter_change', function(e){
    if ($isAutoApply.is(":checked")) {
      startCountdown();
    }
  });
  $(document).on('event_filter_open', function(e){
    if ($isAutoApply.is(":checked")) {
      needUpdate = false;
    }
  });

  $('#statistic-filter-form input[type="text"]').on('click', function() {
    $(this).trigger('event_filter_open');
  });

  $('#statistic-filter-form input[type="text"], #formmodel-decimals').on('change', function() {
    $(this).trigger('event_filter_change');
  });

  // Паттерн Debounce
  function startCountdown() {
    needUpdate = true;
    userInteractionTimer = defaultUserInteractionTimerValue;
  }

  $filterButton.on('click', function (e) {
      var $this = $(this);

      e.preventDefault();
      $filterButton.removeClass('active');
      $this.addClass('active');

      $from.kvDatepicker("setDate", $this.data("start") + ""), $to.kvDatepicker("setDate", $this.data("end") + "");
      if ($statisticPeriod.length) $statisticPeriod.val($this.data('period'));
  });

  $('.input-daterange input').on('change', function() {
    $filterButton.removeClass('active');
    if ($statisticPeriod.length) $statisticPeriod.val('');

    if ($from.val() != undefined) {
      $('[data-from="' + $from.val() + '"][data-to="' + $to.val() + '"]').addClass('active');
    } else {
      $('[data-to="' + $to.val() + '"]').addClass('active');
    }
  });

  /**
   * Подготовка фильтра перед работой с гридами
   */
  function prepareFilter() {
    var transition = $.support.transition;
    $.support.transition = false;
    Cookies.get(filterCookieKey) === 'true' ? $hiddenFilters.collapse('show') : $hiddenFilters.collapse('hide');
    $.support.transition = transition;
  }

  function getSelectedColumns() {
    var selectedColumns;

    // Cookie больше не используются для хранения колонок
    // Скрипт переносит значение в localStorage, если оно есть в cookie
    selectedColumns = Cookies.getJSON(columnsCookieKey);
    if (selectedColumns) {
      setSelectedColumns(selectedColumns);
      Cookies.remove(columnsCookieKey);
      return selectedColumns;
    } else {
      selectedColumns = JSON.parse(localStorage.getItem(columnsCookieKey))
    }

    return selectedColumns;
  }

  function setSelectedColumns(data) {
    localStorage.setItem(columnsCookieKey, JSON.stringify(data));
  }

  /**
   * Подготовка столбцов перед работой с гридами
   */
  function prepareColumns() {
    // Навешиваем клик, чтобы перехватить change у bootstrap-select
    $('.new-columns-templates-select .dropdown-menu li').unbind('click').bind('click', function (e) {
      stopOnModalIconClick(this, e);
    });

    var data = getSelectedColumns() || {};
    var singleOptions = [];

    var transition = $.support.transition;
    $.support.transition = false;

    // Настройка селектпикера
    // Заполняем селект столбцами, которые есть в текущей странице
    $tableFilterSelect.find('option').remove();
    $statisticDataTable.find('thead').find('th').each(function (index) {
      var $this = $(this),
          text = $this.text(),
          code = $this.data('code') || text.hashCode(),
          $option,
          $optgroup,
          group,
          isSelected;
      if ($this.is('.action-column')) return;


      isSelected = !data.hasOwnProperty(code) || data[code];

      $option = $('<option>', {
        value: code,
        text: text,
        selected: isSelected,
        'data-index': index,
        'data-code': $this.data('code'),
        'data-col-seq': $this.data('col-seq'),
        'data-is-group': !$this.data('code')
      });

      if($this.data('group')) {
        $optgroup = $tableFilterSelect.find('optgroup[label="' + $this.data('group') + '"]');
        $optgroup.html() == null
            ? $tableFilterSelect.append($('<optgroup>', {label: $this.data('group')}).append($option))
            : $optgroup.append($option);

        if(isSelected) $optgroup.addClass('selected');
      } else {
        singleOptions.push($option);
      }

      // По умолчанию показываются все столбцы
      if (!data.hasOwnProperty(code)) {
        data[code] = true;
      }
    });
    $tableFilterSelect.prepend(singleOptions);
    $tableFilterSelect.selectpicker('refresh');
    $tableFilterSelect.selectpicker('setStyle', 'btn-xs btn-success');
    setSelectedColumns(data);

    $.support.transition = transition;
  }

  /**
   * Сохранение фильтров в куках
   */
  function saveFilter() {
    Cookies.set(filterCookieKey, $hiddenFilters.hasClass('in') ? 'true' : false, {expires: 365});
  }

  /**
   * Сохранение столбцов в куках
   */
  function saveColumns() {
    var data = getSelectedColumns() || {};

    $tableFilterSelect.find('option').each(function () {
      var $this = $(this);
      var code = $this.val() || $this.text().hashCode();
      data[code] = $this.is(':selected');
    });
    setSelectedColumns(data);
  }

  $tableFilterSelect.selectpicker('render');
  $tableFilterSelect.selectpicker('setStyle', 'btn-xs btn-success');
  $hiddenFilters.on('hidden.bs.collapse shown.bs.collapse', function () {
    saveFilter();
  });

  // Обновление списка столбцов таблицы при изменении селекта отображемых колонок
  // Если событие произойдет несколько раз за 10 мс, таблица обновится только один раз для последнего события
  // Сделано, что бы при изменении селекта скриптами тяжелая операция не выполнялась несколько раз
  var columnsChangeTimeout;
  $tableFilterSelect.on('change', function(e) {
    clearTimeout(columnsChangeTimeout);
    columnsChangeTimeout = setTimeout(function() {
      $tableHideColumnHandler();
      saveColumns();
    }, 10);
  });

  // Добавляем пункт для создания нового шаблона
  var $newTemplateOption = $('<option>', {
    value: 'new-template',
    text: ColumnTemplates.$templatesSelect.data('new-template'),
    selected: false
  }).appendTo(ColumnTemplates.$templatesSelect);
  ColumnTemplates.$templatesSelect.selectpicker('render');
  ColumnTemplates.$templatesSelect.selectpicker('setStyle', ColumnTemplates.$templatesSelect.data('class'));
  // Смена шаблона
  ColumnTemplates.$templatesSelect.on('change', function () {
    var $template = $(this).find('option:selected');
    // При клике на создание нового шаблона
    if ($template.val() === 'new-template') {
      return false;
    }

    ColumnTemplates.toggleTemplateByOption();
  });

  window.stopOnModalIconClick = function (el, e) {
    var $modalIcon = $(el).find('.columns-template-icon'),
      templateId = $modalIcon.data('template-id'),
      selectedVal = ColumnTemplates.$templatesSelect.find('option').eq($(el).data('original-index')).val();
    // При клике на иконку редактирования шаблона отменяем события и тригерим открытие модалки
    if ($(e.target).is($modalIcon) || $($modalIcon).has(e.target).length > 0) {
      e.stopPropagation();
      $('.columns-template-update-modal-button[data-template-id="' + templateId + '"]').trigger('click');
    }
    // При клике на создание нового шаблона
    if (selectedVal === 'new-template') {
      e.stopPropagation();
      $('#new-columns-template-modal').trigger('click');
    }
  };

  // Вывод грида, при этом выбирается хендлер для фильтра
  function init() {
    $tableFilterSelect = $('#table-filter');
    $hiddenFilters = $('#hidden-filters');
    $statisticDataTable = $('#statisticTable');

    // подставляем id шаблона в строку url чтобы она отправилась потом при экспорте грида
    var paramsString = window.location.search;
    var searchParams = new URLSearchParams(paramsString);
    searchParams.set('template', $('#statistic-template').val());
    window.history.pushState({path: searchParams.toString()}, null, '?' + searchParams.toString());

    $('#second-group').remove();

    $('.selectpicker').selectpicker();
    prepareFilter();

    var fixedColumnsCount = window.fixedColumnsCount ? window.fixedColumnsCount : 1;

    prepareColumns();

    var statTr = $statisticDataTable.find('tbody').find('tr');
    if (statTr.length === 1 && statTr.find('td').length === 1) {
      statTr.remove();
    }

    if ($statisticDataTable.is('.data-table')) {
      if (table !== undefined) {
        table.destroy();
      }

      var settings = {
        'searching': false,
        'autoWidth': false,
        'scrollX': true,
        'info': false,
        'dom': '<"top"i>rt<"bottom"fp><"clear">',
        'bJQueryUI': true,
        "oLanguage": {
          "sEmptyTable": $statisticDataTable.data('emptyResult')
        },
        columnDefs: [
          {type: 'date-uk', targets: 0}
        ],
        'paging': false,
        fixedColumns: {
          leftColumns: fixedColumnsCount
        },
        order: [
          [0, 'desc']
        ],
        orderFixed: {
          pre: []
        },

        fnDrawCallback: function () {
          var paginate_box = $(".dataTables_paginate");
          if ($(paginate_box).find(".paginate_button").length <= 3) {
            paginate_box.hide();
          } else {
            paginate_box.show();
          }

          var self = this;

          $tableFilterSelect.find('option').each(function () {
            var column = self.api().column($(this).data('index'));
            column.visible($(this).prop('selected'));
          });
        },
        'paginate': {
          'next': 'sss',
          'previous': '',
          'last': '',
          'first': ''
        },
        'language': {
          'paginate': {
            'next': "<i class='glyphicon glyphicon-menu-right'></i>",
            'previous': "<i class='glyphicon glyphicon-menu-left'></i>",
            'last': '',
            'first': ''
          },
          'info': '',
          'sLengthMenu': ''
        }
      };

      if (fixedColumnsCount > 1) {
        for (var i = 1; i < fixedColumnsCount; i++) {
          settings.columnDefs.push({
            type: 'natural-ci',
            targets: i
          });
        }
      }
      settings.columnDefs.push({type: 'numeric-comma', targets: ['_all']});

      var selected_option = {};
      var opt_l = $tableFilterSelect.parent().find('.dropdown-menu li');

      $tableFilterSelect.parent().find('.dropdown-menu li.selected').filter(':not(.dropdown-header, .divider)').each(function() {
        var index = $(this).data('originalIndex');
        var code = $tableFilterSelect.find('option').eq(index).val();
        selected_option[$(this).data('originalIndex')] = code;
      });

      opt_l.unbind('click').bind('click', function() {
        var $this = $(this);

        var columnIndex = $this.data('originalIndex');
        // Get the column API object
        if(typeof columnIndex === "number") {
          var column = table.column(columnIndex);
          column.visible( ! column.visible() );

          setTimeout(function(){
            if($this.parent().find("li.selected[data-optgroup='"+$this.data('optgroup')+"']:not(.dropdown-header)").length == 0) {
              $this.siblings("li.dropdown-header[data-optgroup='"+$this.data('optgroup')+"']").removeClass('selected');
            } else {
              $this.siblings("li.dropdown-header[data-optgroup='"+$this.data('optgroup')+"']").addClass('selected');
            }
          },10);

        } else {
          var arr = {};

          var select = $tableFilterSelect;
          var select_opt = $tableFilterSelect.find('option');

          $this.parent().find('li[data-optgroup="'+$this.data('optgroup')+'"]:not(.dropdown-header)').each(function() {
            var index = $(this).data('originalIndex');
            var code = $tableFilterSelect.find('option').eq(index).val();
            arr[$(this).data('originalIndex')] = code;
          });

          column_group =  table.columns(Object.keys(arr));
          column_group.visible( !column_group.visible()[0] );
          if($this.hasClass('selected')) {
            $this.removeClass('selected');
            for(var i in arr) {
              if(typeof selected_option[i] !== 'undefined') {
                delete selected_option[i];
              }
            }

          } else {
            $this.addClass('selected');
            selected_option = $.extend(selected_option, arr);
          }

          var selectedVals = Object.keys(selected_option).map(function (key) {
            return selected_option[key];
          });
          $tableFilterSelect.selectpicker('val', selectedVals);
          $tableFilterSelect.trigger('change');
        }
      });

      $tableHideColumnHandler = function() {
        var visible = [];
        var unvisible = [];
        $tableFilterSelect.find('option').each(function() {
          if ($(this).prop('selected')) {
            visible.push($(this).data('index'));
          } else {
            unvisible.push($(this).data('index'));
          }
        });
        table.columns(unvisible).visible(false, false);
        table.columns(visible).visible(true, false);
        table.draw();
      };
    } else if ($statisticDataTable.is('.detail-table')) {

      var $options = $tableFilterSelect.find('option');

      $tableHideColumnHandler = function() {
        var actionColumnsCount = $statisticDataTable.find('th.action-column').length;
        $statisticDataTable.find('tr').each(function() {
          var $columns = $(this).find('td:not([data-disable-hide-column="true"]), th:not([data-disable-hide-column="true"])');
          $options.each(function() {
            var $option = $(this);
            $columns.eq(parseFloat($option.data('index') - actionColumnsCount)).toggleClass('hidden', !$option.is(':selected'));
          });
        });
      };

      $tableHideColumnHandler();
    }

    $('.bootstrap-select.menu-right').each(function() {
      $(this).find('div.dropdown-menu').addClass('dropdown-menu-right');
    });

    $('[data-toggle=popover]').popover({
      delay: { "show": 1200, "hide": 0 }
    });
  }

  $(document).on('pjax:end', function (event, xhr) {
    if (xhr.readyState === 4) init();
  });

  // для все классов с автофильтром всегда применяем фильтры
  $filterForm.on('change', 'select.auto_filter:not(#table-filter), input.auto_filter', function (e) {
    startCountdown();
  });

  $filterForm.find('button[type=submit]').on('click', function (e) {
    startCountdown();
  });

  $filterForm.on('submit', function(e) {
    e.preventDefault();
    if (needUpdate) {
      needUpdate = false;
      $.pjax.submit(e, pjaxSelector, {push: true, timeout: false});
    }
  });

  $("#statisticCurrency").on("mainCurrencyChanged", function(e, newValue){
    $("#hiddenCurrency").val(newValue);
    startCountdown();
  });

  $("#is_auto_refresh").change(function() {
    if($(this).is(":checked")) {
      refreshFormSubmitTimer();
      return;
    }
    stopFormSubmitTimer();
  });

  function startFormSubmitTimer() {
    formAutoSubmitTimer = setInterval(function() {
      $filterForm.find('button[type=submit]').trigger('click');
    }, 300000 /*5min*/);
  }

  function refreshFormSubmitTimer() {
    stopFormSubmitTimer();
    startFormSubmitTimer();
  }

  function stopFormSubmitTimer() {
    clearInterval(formAutoSubmitTimer);
  }

  init();
});

String.prototype.hashCode = function(){
  var hash = 0;
  if (this.length === 0) return hash;
  for (i = 0; i < this.length; i++) {
    hash += '' + this.charCodeAt(i);
  }
  return hash;
};

/**
 * Управление шаблонами столбцов.
 * TODO Закинуть все функции для работы с шаблонами в этот объект (а для управления столбцами создать отдельный объект TemplateColumns)
 */
var ColumnTemplates = {
  /** @const {integer} ID шаблона по умолчанию (значение дублируется из модели шаблона на сервере) */
  DEFAULT_TEMPLATE: -1,

  /** @var {object} Селект шаблонов */
  $templatesSelect: $('#columns-templates'),
  /** @var {object} Селект колонок */
  $columnsSelect: $('#table-filter'),

  /**
   * Получить выбранный в селекте шаблон.
   * Не учитывает значение в куки
   * @return {object} jQuery-объект опции селекта
   */
  getSelectedTemplateAsOption: function() {
    return this.$templatesSelect.find('option:selected');
  },

  /**
   * Запомнить/забыть шаблон
   */
  toggleTemplateByOption: function () {

    // Получаем id выбранного шаблона
    var columnsTemplateId = this.getSelectedTemplateAsOption().val();
    if (typeof columnsTemplateId === 'undefined') {
      columnsTemplateId = this.DEFAULT_TEMPLATE;
      // Если ничего не выбрано, переключаемся на шаблон по умолчанию
      ColumnTemplates.$templatesSelect.val(columnsTemplateId);
      ColumnTemplates.$templatesSelect.selectpicker('refresh');
    }

    $('.new-columns-templates-select button').addClass('active');
    $('.trafficType-select').removeClass('active');

    $('#statistic-template').val(columnsTemplateId);

    $('#statistic-submit-btn').trigger('click');
  }
};