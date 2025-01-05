let subGroupInit = function (config) {
  if (config.query.FormModel === undefined) {
    config.query = {FormModel: {}};
  }

  // Добавляем меню со второй группировкой по-умолчанию
  let $defaultSecondGroup = $('#default-second-group');
  if ($defaultSecondGroup.length > 0) {
    $defaultSecondGroup.empty();
    let $select = $('<select/>', {
      'id': 'default-second-group-select',
      'data-style': 'btn-xs btn-default btn',
      'data-dropdown-align-right': 1,
      'class': 'selectpicker col-i',
    });

    $defaultSecondGroup.append('&nbsp;');
    $defaultSecondGroup.append($select);

    $select.append($('<option/>', {'value': ''}).html('...'));
    for (let k in config.groups) {
      $select.append($('<option/>', {
        'value': k,
      }).html(config.groups[k]));
    }
    $select.selectpicker();
  }

  let $ul = $('<ul/>', {'class': 'dropdown-menu'});

  // Добавляем группировки
  for (let k in config.groups) {
    $ul.append($('<li/>').prepend($('<a/>', {
      'href': 'javascript:void(0);',
      'data-code': k,
      'class': 'showSubtable',
    }).html(config.groups[k])));
  }
  // Добавляем кнопку скрытия
  $ul.append($('<li/>', {'style': 'display: none;'}).prepend($('<a/>', {
    'href': 'javascript:void(0);',
    'data-code': 'hide',
    'class': 'showSubtable'
  }).html(config.hideLabel)));

  $('.groupCell')
    .prepend(
      $('<div/>', {'class': 'btn-group'}).prepend(
        $ul
      ).prepend(
        $('<span/>', {'class': 'showSubtableButton dropdown-toggle', 'data-toggle': 'dropdown'})
      ).prepend(
        $('<span/>', {'class': 'showSubtableSelectedButton active', 'style': 'display: none;'})
      ).prepend(
        $('<span/>', {'class': 'hideSubtableSelectedButton', 'style': 'display: none;'})
      )
    );

  $('.showSubtableButton').unbind('click').bind('click', function(e) {
    let $menu = $(this).parent().find('.dropdown-menu');
    let menuHeight = parseInt($menu.css('height'), 10);
    let mouseX = e.pageX - $(window).scrollLeft() + 15;
    let mouseY = e.pageY - $(window).scrollTop();
    let bottom = $(window).height() - mouseY;

    if (bottom < menuHeight) {
      mouseY -= menuHeight;
    }

    $menu
      .css('position', 'fixed')
      .css('left', mouseX + 'px')
      .css('top', mouseY + 'px');
  });

  $('.showSubtable, .showSubtableSelectedButton').unbind('click').bind('click', function(e) {
    // Группировка для запрашиваемых данных
    let code = $(this).data('code');
    let $row = $(this).closest('tr');
    let $cells = $row.find('td');
    let $hideSubtableSelectedButton = $row.find('.hideSubtableSelectedButton');
    let $showSubtableSelectedButton = $row.find('.showSubtableSelectedButton');

    $showSubtableSelectedButton.removeClass('active').hide();
    $hideSubtableSelectedButton.addClass('active');
    if ($(this).hasClass('showSubtableSelectedButton')) {
      $hideSubtableSelectedButton.show();
    }

    if (code === 'hide') {
      hideSubMenu($row);
      return;
    }
    // Показываем кнопку hide
    $row.find('a[data-code="hide"]').closest('li').show();

    let configClone = JSON.parse(JSON.stringify(config));

    $cells.addClass('subSort');

    // Значение для фильтрации
    let groupValue = $(this).closest('.groupCell').data('value');

    // В данных формы подменяем группировку и поле, по которому ищем
    configClone.query.FormModel.groups = [code];
    configClone.query.FormModel[configClone.searchFields] = groupValue;
    delete configClone.query.sort;

    $cells.unbind('click').bind('click', function(e) {
      // Если кликнули по раскрывашке группировок - выходим
      if($(e.target).is('.showSubtable, .showSubtableButton, .showSubtableSelectedButton, .hideSubtableSelectedButton')) {
        return;
      }

      let query = configClone.query;
      let sort = '-' + $(this).data('code');
      let sortType = 'desc';

      // Если сортировали по этому столбцу, теперь сортируем в обратном порядке
      if (query.sort === sort) {
        sort = sort.slice(1); // убираем - в начале строки
        sortType = 'asc';
      }
      $cells.removeClass('asc desc');
      $(this).addClass(sortType);

      query.sort = sort;

      updateTable($row, query);
    });

    updateTable($row, configClone.query);
  });

};

$(document).on('click', '.hideSubtableSelectedButton', function(e) {
  let $row = $(e.target).closest('tr');
  hideSubMenu($row);
});

$(document).on('click', '.hideSubtable a', function(e) {
  let $row = $(e.target).closest('tr').prevAll(':not(.secondary-group-row)').first();
  hideSubMenu($row);
});

$(document).on('change', '#default-second-group-select', function(e) {
  let val = $(e.target).val();
  let $showSubtable = $('.showSubtableButton');
  let $showSubtableSelected = $('.showSubtableSelectedButton');
  let $hideSubtableSelected = $('.hideSubtableSelectedButton');

  // Если сбросили вторую группировку, прячем '+' и возвращаем '...'
  if (val === '') {
    $showSubtable.show();
    $showSubtableSelected.hide();
    $hideSubtableSelected.hide();
    return;
  }
  // Если выбрали вторую группировку, сворачиваем все расскрытые группировки
  $('.hideSubtableSelectedButton.active').trigger('click');
  // Если выбрали вторую группировку, прячем '...' и возвращаем '+'
  $showSubtable.hide();
  $showSubtableSelected.data('code', val);
  $('.showSubtableSelectedButton.active, .hideSubtableSelectedButton.active').show();
});

function hideSubMenu($row) {
  let $cells = $row.find('td');
  $row.find('.hideSubtableSelectedButton').removeClass('active').hide();
  let $showSubtableSelectedButton = $row.find('.showSubtableSelectedButton');
  $showSubtableSelectedButton.addClass('active');

  if ($('#default-second-group-select').val() !== '') {
    $showSubtableSelectedButton.show();
  }

  // Удаляем подгруженные ранее данные
  $row.nextUntil('tr:not(.secondary-group-row)').remove();
  // Прячем кнопку hide
  $($row).find('td:first-of-type ul.dropdown-menu li a[data-code="hide"]').closest('li').hide();
  $cells.removeClass('asc desc subSort');
  $cells.unbind('click');
}

function updateTable($row, query){
  let url = window.location.pathname + '?' + $.param(query);

  // Запрашиваем новые данные
  $.post(url)
    .done(function (data) {
      // Удаляем подгруженные ранее данные (если есть)
      $row.nextUntil('tr:not(.secondary-group-row)').remove();
      // Вставляем новые
      $row.after(data);
    });
}