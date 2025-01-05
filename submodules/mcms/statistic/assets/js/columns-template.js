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
      allColumns = $('#statistic-data-table').data('template-columns'),
      currentGroup = allColumns[0].group;

    $.each(allColumns, function (index, value) {
      if (value.code) {
        var code = value.code;

        var isChecked = 'checked';
        if (!!templateColumns && templateColumns.indexOf(code) === -1) {
          isChecked = ' ';
        }

        if (value.group !== 'group' && value.group !== currentGroup) {
          var $label = $('<label class="columns-group-label">' + value.groupLabel + '</label>').appendTo($('.columns-template-columns'));

          currentGroup = value.group;
        }

        var $checkbox = $(
          '<div class="checkbox">' +
          '<label>' +
          '<input type="checkbox" class="checkbox template-form-column" name="columns_template_column[]" value="' + code + '" ' + isChecked + '>' +
          '<span>' + value.text + '</span>' +
          '</label>' +
          '</div>'
        ).appendTo($('.columns-template-columns'));
      }
    });
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
                'data-url': '/admin/statistic/column-templates/update/?id=' + res[template].id,
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
