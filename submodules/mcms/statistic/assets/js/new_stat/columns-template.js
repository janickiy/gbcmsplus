/**
 * Форма управления шаблоном
 */
var ColumnsTemplateForm = {
  /** Выбрать все колонки */
  selectAllColumns: function () {
    $('.template-form-column').prop('checked', true);
  },
  /** Cнять выбор со всех колонок */
  unselectAllColumns: function () {
    $('.template-form-column').prop('checked', false);
  },
  /** Сгенерировать сгруппированные чекбоксы для управления полями шаблона */
  generateColumnsFields: function () {
    var templateColumns = $('.columns-template-columns').data('columns'),
      allColumns = $('#statisticTable').data('template-columns'),
      currentGroup = '';

    var categories = {};

    $.each(allColumns, function (index, value) {
      if (!value.code) {
        return;
      }

      if (!categories.hasOwnProperty(value.category)) {
        categories[value.category] = {
          title: value.categoryLabel,
          code: value.category,
          groups: {}
        };
      }

      if (!categories[value.category].groups.hasOwnProperty(value.group)) {
        categories[value.category].groups[value.group] = {
          label: value.groupLabel,
          code: value.group,
          items: []
        }
      }

      var amountItems = categories[value.category].groups[value.group].items.length;

      var isChecked = ' ';
      if (templateColumns && templateColumns.indexOf(value.code) !== -1) {
        isChecked = 'checked';
      }

      categories[value.category].groups[value.group].items[amountItems] = {
        text: value.text,
        code: value.code,
        isChecked: isChecked
      };
    });

    $.each(categories, function (index, value) {
      $('<h1><a href="#" class="columns-template-columns-title" data-target="' + value.code + '">' + value.title + '</a></h1>')
        .appendTo($('.columns-template-columns'));

      var $wrapper = $('<div class="columns-template-columns-container" data-category="' + value.code + '"></div>');
      $wrapper.appendTo('.columns-template-columns');

      $.each(value.groups, function (index, value) {
        var $groupWrapper = $('<div class="columns-template-columns-group-wrapper" data-group="' + value.code + '"></div>');
        $groupWrapper.appendTo($wrapper);

        $('<label class="columns-group-label" data-target="' + value.code + '">' + value.label + '</label>')
          .appendTo($groupWrapper);

        $.each(value.items, function (index, value) {
          $(
            '<div class="checkbox">' +
            '<label>' +
            '<input type="checkbox" class="checkbox template-form-column" name="columns_template_column[]" value="' + value.code + '" ' + value.isChecked + '>' +
            '<span>' + value.text + '</span>' +
            '</label>' +
            '</div>'
          ).appendTo($groupWrapper);
        });
      });
    });

    this.handleCheckboxesEvents();
  },
  handleCheckboxesEvents: function () {
    // TRICKY необходимо проверять, навешивали ли обработчик
    // тк при повторном рендере формы, будет пытаться навесить заново
    if (!window.hasOwnProperty('checkboxEventHandlerIsRun')) {
      window['checkboxEventHandlerIsRun'] = false;
    }

    if (checkboxEventHandlerIsRun === true) {
      return;
    }

    // если все чекбоксы активны, деактивируем
    // иначе активируем все
    var handleChecked = function ($wrapper) {
      var $items = $wrapper.find(':checkbox')
        , isAllChecked = true
      ;

      $.each($items, function (index, checkbox) {
        if ($(checkbox).is(':checked') === false) {
          isAllChecked = false;
          return false; // прерываем цикл
        }
      });

      $items.prop('checked', !isAllChecked);
    };

    $(document)
      .on('click', '.columns-template-columns-title', function (e) {
        e.preventDefault();

        var $this = $(e.target).closest('.columns-template-columns-title')
          , $wrapper = $('[data-category="' + $this.data('target') + '"]')
        ;

        handleChecked($wrapper);
      })
      .on('click', '.columns-group-label', function (e) {
        e.preventDefault();

        var $this = $(e.target).closest('.columns-group-label')
          , $wrapper = $('[data-group="' + $this.data('target') + '"]')
        ;

        handleChecked($wrapper);
      });

    checkboxEventHandlerIsRun = true;
  }
};

/**
 * Сгенерировать контент для option[data-content]
 * @param {object} template
 * @returns {string}
 */
function generateTemplateOptionContent(template) {
  return '<span class="text columns-template-text"><span class="columns-template-name">' +
  template.name +
  '</span><span class="columns-template-icon" title="' + ColumnTemplates.$templatesSelect.data('update-title') +
  '" data-template-id="' + template.id + '">' +
  '<span class="glyphicon glyphicon-cog"></span>' +
  '</span>' +
  '</span>';
}

/**
 * Если ID шаблона указан, функция получает с сервера список столбцов шаблона и применяет на таблицу (если указанный шаблон - текущий)
 * Если ID шаблона не указан, функция обновляет содержимое селекта шаблонов
 * @param {integer} id ID шаблона
 */
function updateColumnsSelector(id) {
  $.ajax({
    type: 'POST',
    url: ColumnTemplates.$templatesSelect.data('get-columns-template-url'),
    dataType: 'json',
    data: {'id': id},
    success: function (res) {
      var currentTemplate = ColumnTemplates.$templatesSelect.val();

      if (!!id) {
        // Выполняется после обновления шаблона (если передан ID)
        // Обновление данных о шаблоне
        var templateOption = ColumnTemplates.$templatesSelect.find('option[value="' + id + '"]');
        templateOption.data('columns', JSON.parse(res.columns));
        // Переименование
        templateOption.data('content', generateTemplateOptionContent(res));
        ColumnTemplates.$templatesSelect.selectpicker('refresh');

        if (currentTemplate == id) {
          // Применение обновленного шаблона на таблицу
          ColumnTemplates.toggleTemplateByOption();
        }
      } else {
        // Обновление списка селекта шаблонов
        // Выполняется после создания или удаления шаблона

        /** @var {array} ID шаблонов отображенных в селекте */
        var templateIds = [];
        ColumnTemplates.$templatesSelect.find('option').each(function () {
          if ($(this).val() !== 'new-template') {
            // Шаблон есть в селекте
            var templateId = parseInt($(this).val());
            templateIds.push(templateId);

            // Шаблон удален, это определено так как шаблон есть в селекте, но нет на сервере.
            // Удаляем его из селекта и из куки (короч шаблон удален)
            if (typeof res[templateId] === 'undefined') {
              $(this).remove();
            }
          }
        });

        // Добавление в селект отсутствующие шаблоны
        for (var template in res) {
          if (res.hasOwnProperty(template) && templateIds.indexOf(parseInt(res[template].id)) === -1) {
            ColumnTemplates.$templatesSelect.find('option[value="new-template"]').before(
              $('<option>', {
                value: res[template].id,
                text: res[template].name,
                'data-columns': res[template].columns,
                'data-content': generateTemplateOptionContent(res[template])
              })
            );
            $('#new-columns-template-modal').before(
              $('<button>', {
                'type': 'button',
                'class': 'hidden columns-template-update-modal-button',
                'data-toggle': 'modal',
                'data-template-id': res[template].id,
                'data-url': '/admin/statistic/new-column-templates/update/?id=' + res[template].id,
                'data-modal-method': 'post',
                'data-target': '#modalWidget'
              })
            );
          }
        }
        ColumnTemplates.$templatesSelect.selectpicker('refresh');

        if (typeof ColumnTemplates.getSelectedTemplateAsOption().val() === 'undefined') {
          // Обнуление шаблона
          ColumnTemplates.toggleTemplateByOption();
        }
      }

      // Переприязываем событие после обновления селекта
      $('.columns-templates-select .dropdown-menu li').on('click', function (e) {
        /** @see statistic.js */
        window.stopOnModalIconClick(this, e);
      });
    }
  });
}

$(document).on('beforeValidate', '#columns-template-form', function () {
  var columns = [];
  $('input[type=checkbox][name="columns_template_column[]"]:checked').each(function () {
    columns.push($(this).val());
  });
  $('#template-columns').val(JSON.stringify(columns));
});
