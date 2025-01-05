function initLanding() {
  var $form = $('#add-landing-form');
  var $landing = $('#sourceoperatorlanding-landing_id');
  var $operator = $('#sourceoperatorlanding-operator_id');
  var $sourceoperatorlanding = $('#sourceoperatorlanding-id');
  var $profitType = $('#sourceoperatorlanding-profit_type');
  var $tbody = $('.container-items tbody');
  var key = $form.data('key');
  var updateUrl = $form.data('update-url');


  // Включаем Select2 для выбора лендингов;
  var select2Options = {
    allowClear: true,
    theme: $landing.data('theme'),
    width: '558px',
    placeholder: $landing.data('placeholder'),
    ajax: {
      dataType: 'json',
      url: $landing.data('url'),
      data: function(params) {
        var operatorId = $operator.val();

        return {
          operatorRequired: true,
          q: params.term ? params.term : '',
          operators: operatorId ? [operatorId] : []
        };
      }
    }
  };
  $landing.select2(select2Options);

  // Сбрасываем лендинг при смене оператора
  $form.on('change', '.selectpicker', function(event) {
    $landing.val('').change();
  });

  $form.on('submit', function(event){
    event.preventDefault();
    $form.unbind('beforeSubmit');

    // Перед сабмитом проверяем на ошибки
    $form.on("beforeSubmit", function (event, messages) {
      if ($form.find('.has-error').length) {
        return false;
      }
      // порядковый номер лендинга
      var rowNumber = (key == undefined) ? $tbody.children('tr').length : key;

      // получаем данные для отображения
      var operatorId = $operator.val();
      var operatorName = $operator.find('option:selected').text();
      var landingId = $landing.val();
      var landingName = '#' + $landing.val() + ' - ' + $landing.find('option:selected').text().replace('#' + $landing.val() + ' - ', '');
      var profitTypeId = $profitType.val();
      var profitTypeName = $profitType.find('option:selected').text();

      var operatorInput = $('<input/>', {
        name:   'SourceOperatorLanding[' + rowNumber + '][operator_id]',
        val:  operatorId,
        type:   'hidden'
      });
      var landingInput = $('<input/>', {
        name:   'SourceOperatorLanding[' + rowNumber + '][landing_id]',
        val:  landingId,
        type:   'hidden'
      });
      var profitTypeInput = $('<input/>', {
        name:   'SourceOperatorLanding[' + rowNumber + '][profit_type]',
        val:  profitTypeId,
        type:   'hidden'
      });

      var editButton = $('<a/>', {
        class:   'update-landing btn btn-xs btn-default',
        href:  '#',
        'data-toggle':   'modal',
        'data-key':   rowNumber,
        'data-url':   updateUrl + '&landingId=' + landingId + '&operatorId=' + operatorId + '&profitType=' + profitTypeId + '&key=' + rowNumber,
        'data-target':   '#modalWidget',
        'data-pjax': 0,
      }).append($('<span/>', {
        class: 'glyphicon glyphicon-pencil'
      }));

      var removeButton = $('<button/>', {
        class:   'remove-item btn btn-xs btn-default',
        type:  'button',
        onclick:  '$(this).closest("tr").remove()',
      }).append($('<span/>', {
        class: 'glyphicon glyphicon-trash'
      }));

      // Добавляем строку в таблицу
      var row = $('<tr/>', { class: 'item' });
      $('<td/>').append(operatorInput).append(operatorName).appendTo(row);
      $('<td/>').append(landingInput).append(landingName).appendTo(row);
      $('<td/>').append(profitTypeInput).append(profitTypeName).appendTo(row);
      $('<td/>').append(editButton).append(removeButton).appendTo(row);
      var $removeRow = $tbody.find('tr').eq(key);
      row .attr('class', $removeRow.attr('class'));

      // key=undefined, значит это добавление (добавляем запись в конец таблицы)
      // иначе заменяем строку с порядковым номером key
      if(key == undefined) {
        $tbody.append(row);
      } else {
        $removeRow.after(row);
        $removeRow.remove();
      }

      // Прячем модалку
      $('#modalWidget').modal('hide');
    });
  });

}