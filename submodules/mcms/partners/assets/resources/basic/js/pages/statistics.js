$(function () {

  Array.prototype.diff = function(a) {
    return this.filter(function(i) {
      return a.indexOf(i) < 0;
    });
  };

  var $startDate = $("#statistic-start_date")
    , $endDate = $("#statistic-end_date")
    , $tableFilterSelect = $('#table_filter')
    , $filterForm = $('#statistic-filter-form')
    , $filterLabel = $('.change_date-period > label')
    , pjaxSelector = '#statistic-pjax'
    , revshareOrCPA = $('#revshareOrCPA')
    , needUpdate = false
    , defaultUserInteractionTimerValue = 1
    , pjaxFormSubmitCounter = 2
    , userInteractionTimer = 1
    , updateTimer = setInterval(function () {
      if (needUpdate && userInteractionTimer == pjaxFormSubmitCounter) {
        $filterForm.trigger('submit');
        needUpdate = false;
      }
      if (needUpdate) {
        userInteractionTimer += 1;
      }
    }, 1000)
    , $tableHideColumnHandler = undefined
    , table = undefined
    ;

  // Паттерн Debounce
  function startCountdown() {
    userInteractionTimer = defaultUserInteractionTimerValue;
    needUpdate = true;
  }

  $('.input-daterange input').on('change', function() {
    $filterLabel.removeClass('active').find('input').prop('checked', false);
    if ($startDate.val() != undefined) {
      $('[data-from="' + $startDate.val() + '"][data-to="' + $endDate.val() + '"]').parent().addClass('active');
    } else {
      $('[data-to="' + $endDate.val() + '"]').parent().addClass('active');
    }
  });

  $tableFilterSelect.on('prepareFields', function(e, revshareOrCPA, table) {

    var rc = revshareOrCPA.val();
    $("ul li[data-optgroup]").show();
    $('.divider').hide();
    if (rc == revshareOrCPA.find('option:eq(2)').val()) {
      $('ul').find('[data-optgroup="2"]').hide();
    }
    if (rc == revshareOrCPA.find('option:eq(1)').val()) {
      $('ul').find('[data-optgroup="3"]').hide();
    }

    $tableFilterSelect.find('option').each(function() {
      var column = table.api().column('[data-column=' + $(this).val() + ']');
      column.visible($(this).prop('selected'));
    });

  });


  function strip(html) {
    var tmp = document.createElement("div");
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || "";
  }

  function init() {
    var stripSpacesRegular = /\s+/g;

    jQuery.extend(jQuery.fn.dataTableExt.oSort, {
      "date-uk-asc": function (a, b) {
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
      },

      "date-uk-desc": function (a, b) {
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
      },

      "int-col-asc": function (a, b) {
        a = parseInt(a.replace(stripSpacesRegular, ''), 10);
        b = parseInt(b.replace(stripSpacesRegular, ''), 10);
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
      },

      "int-col-desc": function (a, b) {
        a = parseInt(a.replace(stripSpacesRegular, ''), 10);
        b = parseInt(b.replace(stripSpacesRegular, ''), 10);
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
      },

      "numeric-comma-pre": function ( a ) {
        a = a.replace(stripSpacesRegular, '');
        var x = (a == "-") ? 0 : a.replace( /,/, "." );
        return parseFloat( x );
      },

      "numeric-comma-asc": function ( a, b ) {
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
      },

      "numeric-comma-desc": function ( a, b ) {
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
      }
    });

    var box = $('#example');
    table_col = box.find('tbody tr').eq(0).find('td').length;
    table_col_width = [];
    table_col_width.push({'width': '10%'});
    for (var i = 1; i < table_col - 1; i++) {
      table_col_width.push({'width': 80 / (table_col - 2) + '%'});
    }
    table_col_width.push({'width': '10%'});

    table = box.DataTable({
        bDestroy: true,
        iDisplayLength: 100,
        searching: false,
        "autoWidth": false,
       /* "scrollX": true,*/
        "info": false,
        "dom": '<"top"i>rt<"bottom"fp><"clear">',
        "order": [[0, "desc"]],

        columnDefs: [
          {type: 'date-uk', targets: 0},
          {type: 'numeric-comma', targets: ['_all']}
        ],


        fnDrawCallback: function () {
          var paginate_box = $(".dataTables_paginate");
          if ($(paginate_box).find(".paginate_button").length <= 3) {
            paginate_box.hide();
          } else {
            paginate_box.show();
          }

          $tableFilterSelect.trigger('prepareFields', [revshareOrCPA, this]);

        },
        "paginate": {
          "next": "",
          "previous": "",
          "last": "",
          "first": ""
        },
        "language": {
          "paginate": {
            "next": "<span aria-hidden='true'>&raquo;</span>",
            "previous": "<span aria-hidden='true'>&laquo;</span>",
            "last": "",
            "first": ""
          },
          "info": "",
          "sLengthMenu": "",
          "emptyTable" : false,
          "zeroRecords" : false
        },
        "fixedHeader": {
          "header": true,
          "footer": true
        }
      });

    var windiw_width = $(window).width();

    var marks = $('#mark').DataTable({
      "bSort" : false,
      bDestroy: true,
      "searching": false,
      'bPaginate': false,
      "autoWidth": false,
      //"scrollX": true,
      "info": false,
      "dom": '<"top"i>rt<"bottom"fp><"clear">',
      fnDrawCallback: function () {
        $tableFilterSelect.trigger('prepareFields', [revshareOrCPA, this]);
      },
      "fixedHeader": {
        "header": true,
        "footer": true
      },
      "language" : {
        "emptyTable" : false,
        "zeroRecords" : false
      }
    });

    var tb_statistic_grid = $('#tb-statistic-grid').DataTable({
      "bSort" : false,
      "searching": false,
      'bPaginate': false,
      "autoWidth": false,
      "info": false,
      "fixedHeader": {
        "header": true
      },
      "language" : {
        "emptyTable" : false,
        "zeroRecords" : false
      }
    });

    function setCollapsePosition() {
      if(windiw_width < 786) {
        var indx = 0;
        new $.fn.dataTable.Responsive( table, {
          details: {
            display: $.fn.dataTable.Responsive.display.childRowImmediate,
            renderer: function ( api, rowIdx, columns ) {
              var group_indx = 0;
              var current_group;
              var data = $.map( columns, function ( col ) {
                var current_group = box.find('tbody tr').not('.child').eq(indx).find('td').eq(col.columnIndex);
                var str = '';

                if (group_indx !== current_group.data('group')) {
                  group_header_label = box.find('thead th[data-group="'+current_group.data('group')+'"]').eq(0).html();
                  str += "</ul>"
                  str += '<div class="toggle-group-visible">'+
                    '<span class="dtr-title">'+group_header_label+'</span>'+
                    '<span class="dtr-data">'+current_group.data('info')+'</span>'+
                    '</div>';
                  str += '<ul class="group_id_'+current_group.data('group')+'">';
                }

                if(current_group.data('group') !== 0) {

                  str += '<li data-dtr-index="'+col.columnIndex+'" data-dt-row="'+col.rowIndex+'" data-dt-column="'+col.columnIndex+'">'+
                    '<span class="dtr-title">'+
                    col.title+
                    '</span> '+
                    '<span class="dtr-data">'+
                    col.data+
                    '</span>'+
                    '</li>';
                }

                group_indx = current_group.data('group');
                return str;

              } ).join('');

              indx++;

              return data ?
                $('<div class="t_mob"/>').append( data ) :
                false;
            }
          }
        });

        var table_footer = box.find('tfoot td');
        if($('.footer__mobile').length === 0 && box.find('tfoot').length > 0) {
          var new_footer = "<div class='footer__mobile'>";
          new_footer += '<div class="toggle__footer-mobile">'+table_footer.eq(0).text()+'</div>';

          var group_i = 0;
          table_footer.each(function(indx, el) {
            if(group_i !== $(el).data('group')) {
              new_footer += '</ul>';
              new_footer += '<div class="footer__mobile-title toggle-group-visible">'+
                '<span class="dtr-title">' + box.find('thead th[data-group="'+$(el).data('group')+'"]').eq(0).html() + '</span>'+
                '<span class="dtr-data">'+$(el).data('info')+
                '</div>';
              new_footer += '<ul class="group_id_'+(group_i+1)+'">';
            }
            if(indx && group_i) {
              new_footer += '<li class="footer__mobile-col"><span class="dtr-title">'+$(el).data('label')+'</span><span class="dtr-data">'+$(el).text()+'</span></li>';
            }

            group_i = $(el).data('group');
          });
          new_footer += '</div>';
          box.after(new_footer);
        }
      }
    }

    function setCollapsePositionMarks() {
      if(windiw_width < 786) {

        var indx = 0;
        new $.fn.dataTable.Responsive( marks, {
          details: {
            display: $.fn.dataTable.Responsive.display.childRowImmediate,
            renderer: function ( api, rowIdx, columns ) {
              var group_indx = 0;
              var current_group;
              var data = $.map( columns, function ( col ) {
                var current_group = $('#mark').find('tbody tr').not('.child').eq(indx).find('td').eq(col.columnIndex);
                var str = '';

                if (group_indx !== current_group.data('group')) {
                  group_header_label = $('#mark').find('thead th[data-group="'+current_group.data('group')+'"]').eq(0).text();
                  str += "</ul>"
                  str += '<div class="toggle-group-visible">'+
                    '<span class="dtr-title">'+group_header_label+'</span>'+
                    '<span class="dtr-data">'+current_group.data('info')+'</span>'+
                    '</div>';
                  str += '<ul class="group_id_'+current_group.data('group')+'">';
                }

                if(current_group.data('group') !== 0) {

                  str += '<li data-dtr-index="'+col.columnIndex+'" data-dt-row="'+col.rowIndex+'" data-dt-column="'+col.columnIndex+'">'+
                    '<span class="dtr-title">'+
                    $(col.title).text()+
                    '</span> '+
                    '<span class="dtr-data">'+
                    col.data+
                    '</span>'+
                    '</li>';
                }

                group_indx = current_group.data('group');
                return str;

              } ).join('');

              indx++;

              return data ?
                $('<div class="t_mob"/>').append( data ) :
                false;
            }
          }
        });

        var table_footer = $('#mark').find('tfoot td');
        if($('.footer__mobile').length === 0 && $('#mark').find('tfoot').length > 0) {
          var new_footer = "<div class='footer__mobile'>";
          new_footer += '<div class="toggle__footer-mobile">'+table_footer.eq(0).html()+'</div>';

          var group_i = 0;
          table_footer.each(function(indx, el) {
            if(group_i !== $(el).data('group')) {
              new_footer += '</ul>';
              new_footer += '<div class="footer__mobile-title toggle-group-visible">'+$('#mark').find('thead th[data-group="'+$(el).data('group')+'"]').eq(0).text()+'</div>';
              new_footer += '<ul class="group_id_'+(group_i+1)+'">';
            }
            if(indx && group_i) {
              new_footer += '<li class="footer__mobile-col"><span class="dtr-title">'+$(el).data('label')+'</span><span class="dtr-data">'+$(el).text()+'</span></li>';
            }

            group_i = $(el).data('group');
          });
          new_footer += '</div>';
          $('#mark').after(new_footer);
        }
      }
    }


   if(box.length != 0)  setCollapsePosition();
   if($('#mark').length != 0)  setCollapsePositionMarks();

    $(window).resize(function () {
      table.columns.adjust().draw();
      marks.columns.adjust().draw();
      windiw_width = $(window).width();
      if(box.length != 0)  setCollapsePosition();
      if($('#mark').length != 0)  setCollapsePositionMarks();
    });

    var selected_option = []
      , values = []
      , opt_l = $('#table_filter').next().find('.dropdown-menu li')
    ;

    opt_l.filter(':not(.dropdown-header, .divider)');

    opt_l.unbind('click').bind('click', function () {

      selected_option = [];
      $tableFilterSelect.parent()
        .find('.dropdown-menu li.selected')
        .filter(':not(.dropdown-header, .divider)')
        .each(function () {
          selected_option.push($(this).data('originalIndex'));
        }
      );

      var $this = $(this);

      var toggle_column = $this.data('originalIndex');

      table = (box.length == 0 ) ? marks : table;

      // Get the column API object
      if (typeof toggle_column == "number") {

        var column = (box.length == 0 ) ? marks.column(toggle_column + $('#mark').data('offset')) : table.column(toggle_column + 1);

        column.visible(!column.visible());

        setTimeout(function () {
          if ($this.parent().find("li.selected[data-optgroup='" + $this.data('optgroup') + "']:not(.dropdown-header)").length == 0) {
            $this.siblings("li.dropdown-header[data-optgroup='" + $this.data('optgroup') + "']").removeClass('selected');
          } else {
            $this.siblings("li.dropdown-header[data-optgroup='" + $this.data('optgroup') + "']").addClass('selected');
          }
        }, 10);

      } else {
        var arr = [];

        $this.parent().find('li[data-optgroup="' + $this.data('optgroup') + '"]:not(.dropdown-header)').each(function () {
          arr.push($(this).data('originalIndex'));
        });

        if ($this.hasClass('selected')) {
          $this.removeClass('selected');
          values = selected_option.diff(arr);

        } else {
          $this.addClass('selected');
          values = selected_option.concat(arr);
        }

        $tableFilterSelect
          .selectpicker('val', values.map(function (val) {
            return val + 1;
          }))
          .trigger('change')
        ;

        $tableFilterSelect.find('option').each(function () {
          var column = table.column('[data-column=' + $(this).val() + ']');
          column.visible($(this).prop('selected'));
        });
      }
    });


    $('.load_content-partial').click(function () {
      var $this = $(this),
        active = $this.hasClass('active');
      $this.parents('table').find('.collapse_tr .collapse-content').slideUp(active ? 0 : 0, function () {
        $(this).parents('.collapse_tr').remove();
        $('.load_content-partial').removeClass('active');
      });

      if (!active) {
        template = $($('.collapse_template tbody').html());
        var tbOs = $this.parents('tr').find('.tb-os');
        $(template).find('.tb-os').html(tbOs.html());
        $(template).find('.tb-os').attr('tb-reason', tbOs.attr('tb-reason'));
        $(template).find('.tb-link').html($this.parents('tr').find('.tb-link').html());
        $(template).find('.tb-land').html($this.parents('tr').find('.tb-land').html());
        $(template).find('.tb-op').html($this.parents('tr').find('.tb-op').html());
        $(template).find('.tb-ua').html($this.parents('tr').find('.tb-ua').html());
        $(template).find('.tb-ref').html($this.parents('tr').find('.tb-ref').html());
        $(template).find('.tb-reason').html($this.parents('tr').find('.tb-reason').html());
        $('.table-collapse_btn span').removeClass('active');
        container = $this.addClass('active').parents('tr').after(template).next('.collapse_tr').find('.collapse-content');
        container.slideDown(0);
      }

    });

    $(".change_date").click(function (e) {
      e.preventDefault();
      $startDate.kvDatepicker("setDate", $(this).data("start"));
      $endDate.kvDatepicker("setDate", $(this).data("end"));
      if (!window.SETTING_AUTO_SUBMIT) {
        startCountdown();
      }
    });
  }

  $(document).on('click', '.toggle-group-visible' , function(e) {
    e.stopPropagation();
    $this = $(this);
    $this.next('ul').stop().slideToggle();
  });

  var dp_mobile_start = $('#m_statistic-start_date');
  var dp_mobile_end = $('#m_statistic-end_date');

  setDpDate('statistic-start_date', true);
  setDpDate('statistic-end_date', true);

  $(document).on('change.dp', '#dp_mobile input', function(e) {
    setDpDate(e.target.id, false);
  });

  $(".change_date-period input").change(function () {
    var start = $(this).data('start');
    var end = $(this).data('end');
    var windiw_width = $(window).width();

    if(start !== undefined && end !== undefined  && windiw_width < 786) {
      $('.dp_container').hide();
    } else {
      $('.dp_container').show();
    }
    $startDate.kvDatepicker("setDate", start + ""), $endDate.kvDatepicker("setDate", end + "");
  });


  $('.selectpicker').selectpicker();

  $("[data-count]").each(function(){

    var a=$(this);
    var defaultTimerValue = a.data("count")
    var total = defaultTimerValue;
    var startTimer = setInterval(timer, 1000);

    $(document).on('pjax:end', function() {
      total = defaultTimerValue;
    });

    timer();

    function timer() {

      total--;
      if (total == 0) {
        $filterForm.trigger('submit');
      }

      var minutes = parseInt(total / 60);
      var seconds = parseInt(total % 60);

      if(seconds < 10) {seconds = "0"+seconds;}

      a.text(minutes + ":" + seconds);

    }

  });

  /**
   * Подготовка фильтра перед работой с гридами
   */
  function prepareFilter() {
    var oldSupportTransition = $.support.transition;
    $.support.transition = false;
    if(Cookies.get('mcms.statistic.showFilter') === 'true') {
      $('#settings').show();
      $('.collapse_filters[data-target="#settings"]').addClass('opened');
    } else {
      $('#settings').hide();
      $('.collapse_filters[data-target="#settings"]').removeClass('opened');
    }
    $.support.transition = oldSupportTransition;

    /**
     * Скрытие периодов не подходящих под текущую группировку.
     * Для группировки по месяцам периоды Сегодня, Вчера и Неделя скрываются.
     * Для группировки по неделям периоды Сегодня и Вчера скрываются.
     */
    $('#statistic-group').on('change', function () {
      var $this = $(this),
        group = $this.val(),
        hiddenPeriods = [],
        $periods = $('.change_date-period label[data-period]');

      // В случае группировки по месяцам, ставим начальную дату SETTINGS_MAX_NUMBER_OF_MONTH месяцев назад, в противном случае 0.5 года назад
      var now = new Date();

      group === 'monthNumbers'
        ? now.setMonth(now.getMonth() - SETTINGS_MAX_NUMBER_OF_MONTH)
        : now.setMonth(now.getMonth() - 6);

      $('#statistic-start_date, #statistic-end_date').kvDatepicker('setStartDate', now);

      if (moment($startDate.val(), 'DD.MM.YYYY').isBefore(moment(now))) {
        $('#statistic-start_date').val(moment(now).format('DD.MM.YYYY'));
      }

      // Определение периодов для скрытия
      if (group == 'monthNumbers') hiddenPeriods = ['today', 'yesterday', 'week'];
      else if (group == 'weekNumbers') hiddenPeriods = ['today', 'yesterday'];

      // Обновление списка периодов
      $periods.show(0).each(function (i, element) {
        var $element = $(element);
        if ($.inArray($element.data('period'), hiddenPeriods) > -1) $element.hide(0);
      });

      // Автоматический выбор первого доступного периода, если активный период был скрыт
      if ($periods.filter('.active:hidden').length > 0) {
        $periods.filter(':visible:first').find('input').prop('checked', true).trigger('change');
      }
    });
  }

  /**
   * Подготовка столбцов перед работой с гридами
   */
  function prepareColumns() {
    var oldSupportTransition = $.support.transition;
    $.support.transition = false;

    var dataString = Cookies.get('mcms.statistic.columns.' + window.location.pathname);
    if (typeof (dataString) === 'string') {
      var data = dataString === '' ? [] : dataString.split(',');
      var optgroup;

      var options = $tableFilterSelect.find('option');
      for (var i = data.length - 1; i >= 0; i--) {
        options.filter('[value=' + data[i] + ']').prop('selected', true);
        optgroup = options.filter('[value=' + data[i] + ']').parent('optgroup').data('optgroup');
        $tableFilterSelect.next().find('li.dropdown-header[data-optgroup=' + optgroup + ']').addClass('selected');
      }
      $tableFilterSelect.selectpicker('render');
    } else {
      $tableFilterSelect.next().find('li.dropdown-header').addClass('selected');
      $tableFilterSelect.selectpicker('selectAll');
    }

    $.support.transition = oldSupportTransition;
  }


  $('.collapse_filters').click(function() {
    var $this = $(this);
    $('.statistics_collapsed').not($(this).data('target')).hide(0);
    $('.collapse_filters').not($(this)).removeClass('opened');
    if($this.hasClass('opened')) {
      $($(this).data('target')).stop().slideUp(300, function() {
        $this.removeClass('opened');
        if ($this.data('target') == '#settings') Cookies.set('mcms.statistic.showFilter', false, {expires: 1});
      });
    } else {
      $this.addClass('opened');
      if ($(this).data('target') == '#settings') Cookies.set('mcms.statistic.showFilter', true, {expires: 1});
      $($(this).data('target')).stop().slideDown(300);
    }
  });

  /**
   * Сохранение столбцов
   */
  $tableFilterSelect.on('change', function() {
    var key = 'mcms.statistic.columns.' + window.location.pathname;
    var data = [];
    $tableFilterSelect.find('option:selected').each(function () {
      data.push($(this).val());
    });
    Cookies.set(key, data.join(','), {expires: 1});
  });


// При сортировке (клик по названию столбца) подставляем данные формы фильтрации в запрос
  $(pjaxSelector).on('pjax:click', function(event, settings) {
    settings.data = $filterForm.serializeArray();
  });

  $(document).on('pjax:end', init);

  $(document).on('pjax:start', function(){
    var tables = $.fn.dataTable.fnTables(true);
    $(tables).each(function () {
      $(this).DataTable().clear().destroy();
    });
  });

  // для все классов с автофильтром всегда применяем фильтры
  $filterForm.on('change', 'select.auto_filter:not(#table-filter), input.auto_filter:not(.styled, .disable_change_trigger)', function (e) {
    startCountdown();
  });
  // для остальных инпутов формы только если установлена настройка auto submit
  if (window.SETTING_AUTO_SUBMIT) {
    $filterForm.on('change', 'select:not(#table-filter, .auto_filter), input:not(.auto_filter, .styled, .disable_change_trigger)', function (e) {
      startCountdown();
    });
  }

  $('body').on('eventClose', function () {
    if (window.SETTING_AUTO_SUBMIT) {
      startCountdown();
    }
  });

  $filterForm.on('submit', function (e) {
    $.pjax.submit(e, pjaxSelector);
  });

  prepareFilter();
  prepareColumns();
  init();

});