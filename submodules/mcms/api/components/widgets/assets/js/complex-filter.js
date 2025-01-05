CF_CONTENT_CLASSNAME = 'complex-filter-content';
CF_NEXTPAGE_CLASSNAME = 'complex-filter-next-page';
CF_NEXTPAGE_BLOCKED_CLASSNAME = 'complex-filter-next-page-blocked';
CF_EXPAND_CLASSNAME = 'complex-filter-expand';
CF_VISIBLE_CLASSNAME = 'complex-filter-visible';
CF_EXPAND_BUTTON_CLASSNAME = 'complex-filter-expand-button';
CF_EXPANDED_CLASSNAME = 'complex-filter-expanded';
CF_PARENT_CHECKBOX_CLASSNAME = 'complex-filter-parent-checkbox';
CF_CHILD_CHECKBOX_CLASSNAME = 'complex-filter-child-checkbox';
CF_SUBMENU_CLASSNAME = 'complex-filter-colapse-submenu';
CF_LABEL_CLASSNAME = 'complex-filter-label';
CF_SUBMENU_LINK_CLASSNAME = 'complex-filter-submenu-link';
CF_PARENT_ITEM = 'complex-filter-parent-item';
CF_CHILD_ITEM = 'complex-filter-child-item';
CF_ROW_CLASSNAME = 'complex-filter-row';
CF_COL_CLASSNAME = 'complex-filter-col';
CF_ROW_CHECKED_CLASSNAME = 'complex-filter-row-checked';
CF_COUNTER_CLASSNAME = 'complex-filter-counter';
CF_WIDGET_CLASSNAME = 'complex-filter-widget';
CF_BUTTON_CLASSNAME = 'complex-filter-dropdown-toggle';
CF_BUTTON_SELECTED_CLASSNAME = 'complex-filter-button-selected';
CF_ADDITIONAL = 'complex-filter-additional';
CF_ITEM_WRAPPER = 'complex-filter-item-wrapper';
CF_CHILD_ITEM_WRAPPER = 'complex-filter-child-item-wrapper';
CF_ACTIVE_LABEL_CLASSNAME = 'complex-filter-active-label';
CF_CLEAR_WIDGET_CLASSNAME = 'complex-filter-widget-clear';
CF_CONTENT_WAITING_CLASSNAME = 'content-waiting';
CF_CHANGED = 'cf_changed';

CF_NEXTPAGE_LABEL = 'Show all &darr;';
CF_BACKPAGE_LABEL = 'Hide &uarr;';

EVENT_FILTER_CHANGE = 'event_filter_change';
EVENT_FILTER_OPEN = 'event_filter_open';

cFilter = function () {
  this.json = {'data': []};
  this._searchFields = null;
  this._customFields = null;
  this._lastSearchQuery = null;
  this.widget = null;
  this.config = {
    data: {},
    widgetId: '',
    searchInputId: '',
    searchButtonId: '',
    formName: '',
    fieldName: '',
    relatedFieldName: '',
    formFieldName: '',
    relatedFormFieldName: '',
    fieldLabelMask: '',
    relatedFieldLabelMask: '',
    searchFields: {},
    isAjax: false,
    searchUrl: '',
    fields: {},
    customFields: {},
    orderFields: {},
    limit: 10,
    relatedLimit: 10
  };


  // Добавить контент (данные из jsonData будут добавленны к уже отрисованым)
  this.addContent = function (jsonData) {
    jsonData = jsonData.filter(function (n) {
      return n !== undefined
    });
    var t = this;
    $.each(jsonData, function (idx, obj) {
      var submenuId = 'submenu_' + t.config.widgetId + '_' + t.config.fieldName + '_' + obj.id;
      var parentRowId = t.config.fieldName + '_' + obj.id;
      // если элемент существует (т.к. отмеченные не удаляем), делаем его видимым (и релевантные элементы подменю) и выходим
      $parentBlock = $('#' + parentRowId);

      var related = obj.hasOwnProperty(t.config.relatedFieldName) ? obj[t.config.relatedFieldName] : [];
      if ($parentBlock.length > 0) {
        // Обновляем кастомные поля
        $parentBlock.find('.' + CF_ADDITIONAL).html(t.getCustomFieldValue(obj));
        $submenuBlock = $parentBlock.find('.' + CF_SUBMENU_CLASSNAME);

        if ($submenuBlock.length > 0) {
          // Удаляем кнопку раскрытия подменю
          $submenuBlock.find('.' + CF_EXPAND_BUTTON_CLASSNAME).remove();
          // Удаляем стили навешеные collapse
          $submenuBlock.find('.' + CF_EXPAND_CLASSNAME).removeAttr("style");

          $.each(related, function (idxRelated, objRelated) {
            var i = 0;
            if (objRelated.isDelete !== true) {
              i++;
            }
            // Показывем релевантные элементы подменю
            $childBlock = $('#' + t.config.relatedFieldName + '_' + objRelated.id);
            // Обновляем кастомные поля
            if ($childBlock.length > 0) {
              $childBlock.find('.' + CF_ADDITIONAL).html(t.getRelatedCustomFieldValue(objRelated));
            }
            // Если блок не отрисован, рисуем
            if ($childBlock.length === 0) {
              t.addSubmenuItem(objRelated, i).appendTo($submenuBlock);
            }
          });
          // имитируем клик по дочернему чекбоксу для того, чтобы обновился счетчик на кнопке
          var $firstChildCheckbox = $parentBlock.find('.' + CF_CHILD_CHECKBOX_CLASSNAME).first();
          $firstChildCheckbox.trigger('change');

          // Если элементов больше лимита, скрываем лишние
          lastElement = $submenuBlock.find('.' + CF_VISIBLE_CLASSNAME + ':eq(' + (t.config.relatedLimit - 1) + ')');
          if (lastElement.length > 0) {
            lastElement.nextUntil().addClass(CF_EXPAND_CLASSNAME).removeClass(CF_VISIBLE_CLASSNAME);
          }
          // Если есть невидимые элементы, добавляем кнопку раскрытия
          if ($submenuBlock.find('.' + CF_EXPAND_CLASSNAME).length > 0) {
            $('<div/>', {'class': 'text-center ' + CF_EXPAND_BUTTON_CLASSNAME}).append(
              $('<a/>', {'href': 'javascript:void(0);'}).html(CF_NEXTPAGE_LABEL)
            ).appendTo($submenuBlock);
          }
        }

        $parentBlock.show();
        return;
      }

      if (related) {
        related = related.filter(function (n) {
          return n !== undefined
        });
      }
      var isSubmenu = related.length > 0;

      $('<div/>', {'class': CF_ROW_CLASSNAME, 'id': parentRowId}).append(
        $('<div/>', {'class': CF_COL_CLASSNAME}).append(
          $('<div/>', {'class': CF_ITEM_WRAPPER}).append(
            $('<div/>', {'class': CF_PARENT_ITEM + (isSubmenu ? '' : ' ' + CF_ACTIVE_LABEL_CLASSNAME)}).append(
              $('<input/>', {
                'type': 'checkbox',
                'value': obj.id,
                'name': t.config.formFieldName,
                'class': CF_PARENT_CHECKBOX_CLASSNAME,
              })
            ).append(
              $('<span/>',
                isSubmenu
                  ? {
                  'data-toggle': 'collapse',
                  'data-target': '#' + submenuId,
                  'class': CF_SUBMENU_LINK_CLASSNAME + ' ' + CF_LABEL_CLASSNAME
                }
                  : {'class': CF_LABEL_CLASSNAME})
                .html(t.getLabel(t.config.fieldLabelMask, obj))
            )
          ).append(
            $('<div/>', {'class': CF_ADDITIONAL}).html(t.getCustomFieldValue(obj))
          )
        ).append(
          $('<div/>', isSubmenu ? {'class': 'collapse ' + CF_SUBMENU_CLASSNAME, 'id': submenuId} : {})
        )).appendTo('#' + t.config.widgetId + ' .' + CF_CONTENT_CLASSNAME);

      if (isSubmenu) {
        var i = 0;
        var hasDeleted = false;
        $.each(related, function (idxRelated, objRelated) {
          if (objRelated.isDelete === true) {
            hasDeleted = true;
          } else {
            i++;
            objRelated.isDelete = false;
          }
          // Если вложенных элементов больше лимита или элемент удален (нерелевантный поисковому запросу), прячу их
         t.addSubmenuItem(objRelated, i).appendTo('#' + submenuId);
        });
        if (i > t.config.relatedLimit || hasDeleted) {
          $('<div/>', {'class': 'text-center ' + CF_EXPAND_BUTTON_CLASSNAME}).append(
            $('<a/>', {'href': 'javascript:void(0);'}).html(CF_NEXTPAGE_LABEL)
          ).appendTo('#' + submenuId);
        }
      }
    });
  };
  //
  this.addSubmenuItem = function (objRelated, order) {
    var childRowId = this.config.relatedFieldName + '_' + objRelated.id;
    return $('<div/>', {
      'class': order > this.config.relatedLimit || objRelated.isDelete === true ? CF_EXPAND_CLASSNAME : CF_VISIBLE_CLASSNAME,
      'id': childRowId
    }).append(
      $('<div/>', {'class': CF_CHILD_ITEM_WRAPPER}).append(
        $('<div/>', {'class': CF_CHILD_ITEM + ' ' + CF_ACTIVE_LABEL_CLASSNAME}).append(
          $('<input/>', {
            'type': 'checkbox',
            'value': objRelated.id,
            'class': CF_CHILD_CHECKBOX_CLASSNAME
          })
        ).append(
          $('<input/>', {
            'type': 'hidden',
            'name': this.config.relatedFormFieldName,
            'value': objRelated.id,
            'disabled': true
          })
        ).append(
          $('<span/>', {'class': CF_LABEL_CLASSNAME}).html(this.getLabel(this.config.relatedFieldLabelMask, objRelated))
        )
      ).append(
        $('<div/>', {'class': CF_ADDITIONAL}).html(this.getRelatedCustomFieldValue(objRelated))
      )
    );
  };
  // Отрисовываем ссылку на следующую страницу, если она есть
  this.addNextPageLink = function (nextPage) {
    nextPage = nextPage.toString();
    content = (nextPage === '')
      ? ''
      : $('<a/>', {'href': 'javascript:void(0);', 'data-url': nextPage}).html(CF_NEXTPAGE_LABEL);

    $('.' + CF_NEXTPAGE_CLASSNAME).html(content);
  };
  // Добавить данные
  this.addData = function (data, nextPage) {
    this.json.data = this.json.data.concat(data);
    // Если данные получаем аяксом, отрисовываем ссылку на следующую страницу
    if (this.config.isAjax && nextPage) {
      this.addNextPageLink(nextPage);
    }

    this.addContent(data);
  };
  // Очистка всего, кроме чекнутых. Чекнутые прячем
  this.clearContent = function (hideChecked) {
    $('#' + this.config.widgetId + ' .' + CF_ROW_CLASSNAME + ':not(.' + CF_ROW_CHECKED_CLASSNAME + ')').remove();
    $('#' + this.config.widgetId + ' .' + CF_ROW_CHECKED_CLASSNAME + ' input[type="checkbox"]:not(:checked)').closest('.' + CF_EXPAND_CLASSNAME + ', .' + CF_VISIBLE_CLASSNAME).remove();
    if (hideChecked) {
      $('#' + this.config.widgetId + ' .' + CF_ROW_CHECKED_CLASSNAME).hide();
    }
  };

  this.reset = function () {
    this.clearContent(true);
    this.addContent(this.json.data);
  };

  // Ajax Поиск по строке
  this.ajaxSearch = function (hideChecked) {
    if (this.config.searchUrl === null || this.config.searchUrl === undefined) {
      return;
    }
    // Строка поиска
    var query = $('#' + this.config.searchInputId).val().trim();
    // Фильтры
    var data = $('#' + this.config.widgetId).closest('form').serialize().split(this.config.formName).join('filters');

    var $widget = $(this.widget);
    // навешиваем класс content-waiting
    $widget.find('.' + CF_CONTENT_CLASSNAME).addClass(CF_CONTENT_WAITING_CLASSNAME);

    $.ajax({
      type: "POST",
      global: false,
      context: this,
      url: this.config.searchUrl + '?' + $.param({
        'fields': this.config.fields.join(','),
        'custom_fields': this.config.customFields.join(','),
        'search_fields': this.config.searchFields.join(','),
        'search': query,
        // Если введен поисковый запрос, сортировка не передается, т.к. работает сортировка по релевантности
        'order_fields': query === '' ? this.config.orderFields.join(',') : '',
        'limit': this.config.limit,
        'offset': 0,
        'depth': 2,
      }),
      data: data,
      success: function (json) {
        this.json.data = json.data;
        this.jsSearch(query, hideChecked);
      }
    }).done();

  };
  // JS Поиск по строке
  this.jsSearch = function (query, hideChecked) {
    this._lastSearchQuery = query;

    query = query.trim().toLowerCase();
    var $widget = $(this.widget);
    var $content = $widget.find('.' + CF_CONTENT_CLASSNAME);

    // клонирование объекта
    var cachedJsonData = $.extend(true, [], this.json.data);

    for (var j = 0; j < this.json.data.length; j++) {
      var relatedFound = false;
      var firstFound = false;

      // проверяем, есть ли вложенные
      if (this.json.data[j].hasOwnProperty(this.config.relatedFieldName)) {
        // проход по элементам второго уровня вложености
        for (var k = 0; k < this.json.data[j][this.config.relatedFieldName].length; k++) {
          var found = false;

          for (var i = 0; i < this.getSearchFields().relatedFields.length; i++) {
            var value = this.json.data[j][this.config.relatedFieldName][k][this.getSearchFields().relatedFields[i]];
            if (value === undefined) {
              value = '';
            }

            value = value.toString().toLowerCase();
            if (query === '' || value.indexOf(query) !== -1) {
              found = true;
              relatedFound = true;
              break;
            }
          }

          // Если совпадение не нашли, удаляем
          if (found === false) {
            cachedJsonData[j][this.config.relatedFieldName][k].isDelete = true;
          }
        }
        // Если нашли во вложеных элементах нет смысла искать в родительских
        if (relatedFound === true) {
          continue;
        }
      }

      // проход по элементам первого уровня вложености
      for (var i = 0; i < this.getSearchFields().fields.length; i++) {
        var value = this.json.data[j][this.getSearchFields().fields[i]];
        if (value === undefined) {
          value = '';
        }
        value = value.toString().toLowerCase();
        if (query === '' || value.toLowerCase().indexOf(query) !== -1) {
          firstFound = true;
          break;
        }
      }
      // Если совпадение не нашли, удаляем
      if (firstFound === false) {
        delete cachedJsonData[j];
      }
    }
    // убираем класс content-waiting
    $content.removeClass(CF_CONTENT_WAITING_CLASSNAME);
    this.clearContent(hideChecked);
    this.addContent(cachedJsonData);
    // Раскрываем видимые
    if (query !== '') {
      $('#' + this.config.widgetId + ' .' + CF_ROW_CLASSNAME + ':visible .collapse').collapse('show');
    }
  };
  // Получить объект с полями для поиска. Отдельно поля родительского объекта и вложенного
  this.getSearchFields = function () {
    if (this._searchFields !== null) {
      return this._searchFields;
    }
    fields = [];
    relatedFields = [];

    for (var i = 0; i < this.config.searchFields.length; i++) {
      // Если есть двойная земля, значит нужно проверять вложенный массив
      if (this.config.searchFields[i].indexOf('__') !== -1) {
        related = this.config.searchFields[i].split('__');
        if (related[0] === this.config.relatedFieldName) {
          relatedFields = relatedFields.concat(related[1]);
        }
        continue;
      }
      fields = fields.concat(this.config.searchFields[i]);
    }
    this._searchFields = {'fields': fields, 'relatedFields': relatedFields};
    return this._searchFields;
  };
  // Получить объект с названиями доп. полей. Отдельно поля родительского объекта и вложенного
  this.getCustomFields = function () {
    if (this._customFields !== null) {
      return this._customFields;
    }
    var fields = [];
    var relatedFields = [];

    for (var i = 0; i < this.config.customFields.length; i++) {
      // Если есть двойная земля, значит нужно проверять вложенный массив
      if (this.config.customFields[i].indexOf('__') !== -1) {
        related = this.config.customFields[i].split('__');
        if (related[0] === this.config.relatedFieldName) {
          relatedFields = relatedFields.concat(related[1]);
        }
        continue;
      }
      fields = fields.concat(this.config.customFields[i]);
    }
    this._customFields = {'fields': fields, 'relatedFields': relatedFields};
    return this._customFields;
  };
  this.getCustomFieldValue = function(obj) {
    var fieldName = this.getCustomFields().fields[0];
    if(fieldName === undefined || obj[fieldName] === undefined) {
      return '';
    }
    return this.getFormattedValue(obj[fieldName], this.config.customFieldFormatter);
  };
  this.getRelatedCustomFieldValue = function(obj) {
    var fieldName = this.getCustomFields().relatedFields[0];
    if(fieldName === undefined || obj[fieldName] === undefined) {
      return '';
    }
    return this.getFormattedValue(obj[fieldName], this.config.relatedCustomFieldFormatter);
  };
  this.getFormattedValue = function(value, formatter) {
    if (!formatter) {
      return value;
    }
    if (typeof formatter === 'string') {
      formatter = [formatter];
    }
    var methodName = 'as' + formatter[0].charAt(0).toUpperCase() + formatter[0].substr(1).toLowerCase();
    var params = $.extend(true, [], formatter);
    params[0] = value;
    return rgk.formatter[methodName].apply(rgk.formatter, params);
  };
  // Получить лейбл для чекбокса по маске
  this.getLabel = function (mask, obj) {
    // Проверка условий
    // Можно делать лейблы типа {if url}la-la-la{/if}. Строка 'la-la-la' будет показана, если url не пустой
    mask = mask.replace(/\{if ([^\}]*?)\}(.*?)\{\/if\}/g, function (str, p1, p2) {
      result = '';
      if (obj[p1] !== '') {
        result = p2;
      }
      return result;
    });

    return mask.replace(/\{([^\}]*?)\}/g, function (str, p1) {
      return obj[p1];
    });
  };

  this.unCheckAll = function () {
    var $widget = $(this.widget);
    var $checkboxes = $widget.find('input[type="checkbox"]');
    $checkboxes.prop('checked', false);

    var $rows = $widget.find('.' + CF_ROW_CLASSNAME);
    $.each($rows, function (i, row) {
      $(row).removeClass(CF_ROW_CHECKED_CLASSNAME);
    });

    counterUpdate($widget);
  };

  /**
   * Сортирует элементы в порядке их чекнутости
   */
  this.sortItems = function () {
    var $widget = $(this.widget);
    var $wrapper = $widget.find('.' + CF_CONTENT_CLASSNAME);
    var $items = $wrapper.find('.' + CF_ROW_CLASSNAME);
    var childItemsClass = '.' + CF_EXPAND_CLASSNAME + ', .' + CF_VISIBLE_CLASSNAME;
    var $lastCheckedItem = null;
    var $lastFullCheckedItem = null;

    // если нет дочерних и а самих элементов не больше 10 - не сортируем
    if (!$items.find(childItemsClass).length && $items.length <= 10) {
      return;
    }

    $.each($items, function (i, item) {
      var $item = $(this);
      var $input = $item.find('.' + CF_PARENT_CHECKBOX_CLASSNAME);
      var $childWrapper = $item.find('.' + CF_SUBMENU_CLASSNAME);
      var $childItems = $item.find(childItemsClass);
      var $childLastTopItem = null;
      var isChildChecked = false;
      var isItemChecked = $input && $input.is(':checked');

      $.each($childItems, function () {
        var $childItem = $(this);
        var $childItemInput = $childItem.find('.' + CF_CHILD_CHECKBOX_CLASSNAME);

        // если не чекнут - уходим
        if (!$childItemInput || !$childItemInput.is(':checked')) {
          return;
        }

        isChildChecked = true;

        if ($childLastTopItem === null) {
          // если это первый чекнутый элемент - помещаем на самый верх
          $childItem.prependTo($childWrapper);
        } else {
          // если не первый - помещаем под предыдущим чекнутым
          $childItem.insertAfter($childLastTopItem);
        }

        $childLastTopItem = $childItem;
      });

      // если нет дочерних чекнутых и сам не чекнут - выходим
      if (!isChildChecked && !isItemChecked) {
        return;
      }

      // если чекнут сам элемент, помещаем его после последнего полностью чекнутого
      if (isItemChecked) {
        if ($lastFullCheckedItem === null) {
          // если это первый полностью чекнутый элемент - помещаем на самый верх
          $item.prependTo($wrapper);
        } else {
          // если не первый - помещаем под предыдущим чекнутым
          $item.insertAfter($lastFullCheckedItem);
        }

        $lastFullCheckedItem = $item;
        return;
      }

      // если чекнуты только дочерние, помещаем в самый конец чекнутых
      if (isChildChecked) {
        if ($lastCheckedItem === null) {
          // если нет частично чекнутых, берем полностью чекнутый
          $lastCheckedItem = $lastFullCheckedItem;
        }

        if ($lastCheckedItem === null) {
          // если это первый частично чекнутый элемент - помещаем на самый верх
          $item.prependTo($wrapper);
        } else {
          // если не первый - помещаем под предыдущим чекнутым
          $item.insertAfter($lastCheckedItem);
        }

        $lastCheckedItem = $item;
      }
    });
  };

  // необходимо, чтобы не запускался поиск по нажатию кнопки, если до этого нажали Enter
  this.getLastSearchQuery = function () {
    return this._lastSearchQuery;
  };

  this.init = function (config) {
    this.addObject(this);
    this.config = config;

    this.widget = document.getElementById(this.config.widgetId);
    this.widget.cFilter = this;

    this.addData(this.config.data, this.config.nextPage);
    this.checkCurrent();
    // Снимаю обработчик события с поля поиска, чтобы лишний раз не триггерилось событие обновления фильтров
    $('.complex-filter-search').unbind('change');
  };
  // Отмечаем чекбоксы в фильтрах согласно GET-запросу
  this.checkCurrent = function() {
    var getParams = new URLSearchParams(location.search.slice(1));

    var values = getParams.getAll(this.config.formName + '[' + this.config.fieldName + '][]');
    var relatedValues = getParams.getAll(this.config.formName + '[' + this.config.relatedFieldName + '][]');

    for (var i = 0; i < values.length; i++) {
      $('#' + this.config.fieldName + '_' + values[i] + ' .' + CF_PARENT_CHECKBOX_CLASSNAME).prop('checked', true).trigger('change');
    }
    for (var i = 0; i < relatedValues.length; i++) {
      $('#' + this.config.relatedFieldName + '_' + relatedValues[i] + ' .' + CF_CHILD_CHECKBOX_CLASSNAME).prop('checked', true).trigger('change');
    }
  };

  this.delay = (function () {
    timer = 0;
    return function (callback, ms) {
      clearTimeout(timer);
      timer = setTimeout(callback, ms);
    };
  })();
};
cFilter.prototype.objects = [];
cFilter.prototype.addObject = function (obj) {
  cFilter.prototype.objects.push(obj);
};
cFilter.prototype.updateAll = function (widgetId) {
  var objects = cFilter.prototype.objects;
  for (var i = 0; i < objects.length; i++) {
    // текущий виджет не обновляем
    if (objects[i].config.widgetId !== widgetId) {
      objects[i].ajaxSearch(false);
    }
  }
};
// Обновление кастомных полей при изменении шаблона
cFilter.prototype.updateCustomFields = function (customField) {
  var update = true;
  var objects = cFilter.prototype.objects;
  for (var i = 0; i < objects.length; i++) {
    // Если есть такой элемент, значит это кастомное поле - текущее. Выходим без обновления виджетов
    if (objects[i].config.customFields.indexOf(customField) !== -1) {
      update = false;
      break;
    }
    // Если нет кастомных полей, идем дальше
    if (objects[i].config.customFields.length === 0) {
      continue;
    }
    // затираем кеш
    objects[i]._customFields = null;
    objects[i].config.orderFields = cFilter.prototype.updateOrderFields(customField, objects[i].config.orderFields, objects[i].config.relatedFieldName);
    objects[i].config.customFields = [customField];
    if (objects[i].config.relatedFieldName) {
      objects[i].config.customFields.push(objects[i].config.relatedFieldName + '__' + customField);
    }
  }
  if (update) {
    cFilter.prototype.updateAll();
  }
};
// Замена сортировок после смены шаблона
cFilter.prototype.updateOrderFields = function (customField, orderFields, relatedFieldName) {
  var allCustomFields = ['cpaRevenue', 'revshareRevenue', 'otpRevenue', 'totalRevenue'];
  for (var k=0; k<allCustomFields.length; k++) {
    var index = orderFields.indexOf(allCustomFields[k]);
    if (index !== -1) {
      orderFields[index] = customField;
    }
    var indexDesk = orderFields.indexOf('-' + allCustomFields[k]);
    if (indexDesk !== -1) {
      orderFields[indexDesk] = '-' + customField;
    }

    if (relatedFieldName) {
      var relatedIndex = orderFields.indexOf(relatedFieldName + '__' + allCustomFields[k]);
      if (relatedIndex !== -1) {
        orderFields[relatedIndex] = relatedFieldName + '__' + customField;
      }
      var relatedIndexDesk = orderFields.indexOf('-' + relatedFieldName + '__' + allCustomFields[k]);
      if (relatedIndexDesk !== -1) {
        orderFields[relatedIndexDesk] = '-' + relatedFieldName + '__' + customField;
      }
    }
  }
  return orderFields;
};

// Открытие-закрытие фильтра
$(document).on('click', '.' + CF_BUTTON_CLASSNAME, function (e) {
  var $widget = $(e.target).closest('.' + CF_WIDGET_CLASSNAME);
  var cFilter = $widget[0].cFilter;

  cFilter.sortItems();
  $widget.find('.' + CF_CONTENT_CLASSNAME).scrollTop(0);

  // Если открыли фильтр, триггерим соответствующее событие
  if ($widget.find('.complex-filter-dropdown-menu').has(':visible').length > 0) {
    $widget.trigger(EVENT_FILTER_OPEN);
  }
});
$(document).click(function (e) {
  var dropdown = $('.complex-filter-dropdown-menu');
  var currentDropdown = $(e.target).closest('.' + CF_WIDGET_CLASSNAME).find('.complex-filter-dropdown-menu');
  var button = $('.' + CF_BUTTON_CLASSNAME);

  var isToHide = !dropdown.is(e.target) && dropdown.has(e.target).length === 0 && dropdown.has(':visible').length > 0;

  // Если есть открытые дропдауны и кликнули не по самому дропдауну,
  // значит дропдаун свернется. Посылаем запросы на обновления фильтров
  if (isToHide) {
    var $widget = dropdown.has(':visible').closest('.' + CF_WIDGET_CLASSNAME);
    var widgetId = $widget.attr('id');
    if ($widget.hasClass(CF_CHANGED)) {
      // Если не меняли, не обновляем
      $widget.trigger(EVENT_FILTER_CHANGE);
      $widget.removeClass(CF_CHANGED);
    }
  }

  // Если кликнули вне дропдауна - прячем
  if (isToHide && !button.is(e.target) && button.has(e.target).length === 0) {
    dropdown.hide();
  }
  // Если кликнули по кнопке - тоглим текущий дропдаун, остальные прячем
  if (button.is(e.target) || button.has(e.target).length > 0) {
    dropdown.not(currentDropdown).hide();
    currentDropdown.toggle();
  }
});

$(document).on('click', '.' + CF_EXPAND_BUTTON_CLASSNAME + ' a', function (e) {
  $(e.target).toggleClass(CF_EXPANDED_CLASSNAME);
  var label = $(e.target).hasClass(CF_EXPANDED_CLASSNAME) ? CF_BACKPAGE_LABEL : CF_NEXTPAGE_LABEL;
  $(e.target).html(label);
  $(e.target).closest('.' + CF_SUBMENU_CLASSNAME).find('.' + CF_EXPAND_CLASSNAME).toggle();
});

$(document).on('change', '.' + CF_PARENT_CHECKBOX_CLASSNAME, function (e) {
  var $widget = $(e.target).closest('.' + CF_WIDGET_CLASSNAME);
  $widget.addClass(CF_CHANGED);
  var isParentChecked = $(e.target).prop('checked');

  // Ставим или снимаем метку, что есть отмеченые
  var $row = $(e.target).closest('.' + CF_ROW_CLASSNAME);
  var hasClass = $row.hasClass(CF_ROW_CHECKED_CLASSNAME);
  if (isParentChecked && !hasClass) {
    $row.addClass(CF_ROW_CHECKED_CLASSNAME);
  }
  else if (!isParentChecked && hasClass) {
    $row.removeClass(CF_ROW_CHECKED_CLASSNAME);
  }

  // Если отмечаем родительский чекбокс, все дочерние тоже отмечаются. И наоборот
  $(e.target).closest('.' + CF_ITEM_WRAPPER).nextAll('.' + CF_SUBMENU_CLASSNAME).find('.' + CF_CHILD_CHECKBOX_CLASSNAME).prop('checked', isParentChecked);
  counterUpdate($(e.target));
  // Дисейблим неотмеченые дочерние фильтры в случае если отмечен родительский либо дочерний не отмечен
  // Сделано для того, чтобы не слать лишние данные в запросе
  $(e.target).closest('.' + CF_ITEM_WRAPPER).nextAll('.' + CF_SUBMENU_CLASSNAME).find('input[type="hidden"]').each(function () {
    var isChildChecked = $(this).parent().find('.' + CF_CHILD_CHECKBOX_CLASSNAME).prop('checked');
    var isDisabled = isParentChecked || !isChildChecked;
    $(this).attr('disabled', isDisabled);
  });
});

$(document).on('change', '.' + CF_CHILD_CHECKBOX_CLASSNAME, function (e) {
  var $widget = $(e.target).closest('.' + CF_WIDGET_CLASSNAME);
  $widget.addClass(CF_CHANGED);
  var currentIsChecked = $(e.target).prop('checked');

  var $row = $(e.target).closest('.' + CF_ROW_CLASSNAME);
  var hasClass = $row.hasClass(CF_ROW_CHECKED_CLASSNAME);
  // Ставим метку, что есть отмеченые
  if (currentIsChecked && !hasClass) {
    $row.addClass(CF_ROW_CHECKED_CLASSNAME);
  }

  // Если отмечены все дочерние чекбоксы, отмечаем родительский
  var checkedAll = true;
  // Если сняты чекбоксы со всех дочерних, снимаем метку с родительского
  var uncheckedAll = true;
  $(e.target).closest('.' + CF_SUBMENU_CLASSNAME).find('.' + CF_CHILD_CHECKBOX_CLASSNAME).each(function () {
    var isChecked = $(this).prop('checked');
    // Меняем активность фильтра в зависимости от состояния чекбокса (чтобы не слать лишние данные в запросе)
    $(this).parent().find('input[type="hidden"]').attr('disabled', !isChecked);
    if (!isChecked) {
      checkedAll = false;
    } else {
      uncheckedAll = false;
    }
  });
  $(e.target).closest('.' + CF_SUBMENU_CLASSNAME).prevAll('.' + CF_ITEM_WRAPPER).find('.' + CF_PARENT_CHECKBOX_CLASSNAME).prop('checked', checkedAll);
  // Снимаем метку, что есть отмеченые
  if (uncheckedAll && hasClass) {
    $row.removeClass(CF_ROW_CHECKED_CLASSNAME);
  }
  counterUpdate($(e.target));

  // Сделано для того, чтобы не слать лишние данные в запросе
  // Дисейблим неотмеченые дочерние фильтры в случае если отмечены все (и выше включили родительский)
  if (checkedAll) {
    $(e.target).closest('.' + CF_SUBMENU_CLASSNAME).find('input[type="hidden"]').attr('disabled', true);
  }
});

// Обновление счетчика фильтра
function counterUpdate($t) {
  var $widget = $t.closest('.' + CF_WIDGET_CLASSNAME);
  var $counter = $widget.find('.' + CF_COUNTER_CLASSNAME);
  var $button = $widget.find('.' + CF_BUTTON_CLASSNAME);
  var $rows = $widget.find('.' + CF_ROW_CLASSNAME);
  var count = 0;
  var allCount = 0;

  $.each($rows, function () {
    if ($(this).find('input[type="checkbox"]:checked.' + CF_PARENT_CHECKBOX_CLASSNAME).length > 0) {
      count++;
      allCount++;
      return;
    }
    if ($(this).find('input[type="checkbox"]:checked').length > 0) {
      allCount++;
    }
  });

  var countText = count > 0 ? '(' + count + ')' : '';

  if (allCount > 0) {
    $button.addClass(CF_BUTTON_SELECTED_CLASSNAME);
  } else {
    $button.removeClass(CF_BUTTON_SELECTED_CLASSNAME);
  }
  $counter.html(countText);
}

$(document).on('click', '.' + CF_ACTIVE_LABEL_CLASSNAME, function (e) {
  if (!$(e.target).is('input[type="checkbox"]')) {
    $(this).find('input[type="checkbox"]').trigger('click');
  }
});

$(document).on('click', '.' + CF_CLEAR_WIDGET_CLASSNAME, function (e) {
  var $widget = $(e.target).closest('.' + CF_WIDGET_CLASSNAME);
  var cFilter = $widget[0].cFilter;

  cFilter.unCheckAll();
  cFilter.reset();

  $widget.trigger(EVENT_FILTER_CHANGE);
});

$(document).on(EVENT_FILTER_CHANGE, function (e) {
  var widgetId = $(e.target).attr('id');
  cFilter.prototype.updateAll(widgetId);
});