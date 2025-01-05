//Фильтр лендов
var animationFlag = true;
var isIsotopeActive = false;
var isotope_options = {
  //Настройка isotope для table шаблона
  table: {
    // options
    itemSelector: 'li.item',
    layoutMode: 'vertical',
    hiddenStyle: {
      opacity: 0
    },
    visibleStyle: {
      opacity: 1
    },
    transitionDuration: animationFlag ? '0.6s' : '0s',
    getSortData: {
      number: function(el) {
        return parseInt($(el).find('.number').html());
      },
      date: function(el) {
        var date_str = $(el).find('.date__create').html();
        var date_arr = date_str.split('.');
        return parseInt(date_arr[2] + date_arr[1] + date_arr[0]);
      },
      number_1: function(el) {
        return parseInt($(el).find('.number_1').html());
      },
      rate: function(el) {
        var rate_str = $(el).find('.addLinks__rate i').html();
        return parseInt(rate_str);
      },
    }
  },
  //Настройка isotope для column шаблона
  column: {
    // options
    itemSelector: 'li.item',
    transitionDuration: animationFlag ? '0.6s' : '0s',
    getSortData: {
      byDataSort: function(el) {
        return parseInt($(el).data('sort'));
      }
    },
    sortBy: 'byDataSort',
    sortAscending: false
  }
};

var block_obj = $('.steps'),
  $container,
  offset,
  blockObjWidth,
  countryBox = $(".addLinks__country"),
  $stepsWrap = $('.steps_wrap'),
  $formStep2 = $('#linkStep2Form'),
  $linkIdInput = $formStep2.find('input[name="LinkStep2Form[id]"]'),
  $streamSelect = $('#linkstep1form-stream_id'),
  $isNewStream = $('#linkstep1form-isnewstream'),
  $landingModal = $('#linkLandingModal'),
  $requestModal = $('#linkRequestModal'),
  $ajaxContainer = $('.ajax_container'),
  $ajaxContainerHeader = $ajaxContainer.find('.grid__header'),
  $ajaxContainerLandings = $ajaxContainer.find('ul.grid'),
  $allCountries = $('.addLinks__country-l2 > li'),
  $categoryFilterItems = $('.addLinks__r-category li'),
  $offerFilterItems = $('.addLinks__r-offer li'),
  paytypeArray = [],
  offerArray = [],
  $paytypesList = $('#paytypesList'),
  $paytypesListOptionsCount = $paytypesList.find('option').length,
  $allOss = $('.addLinks__country-l3 li'),
  $allCountries = $('.addLinks__country-l2 > li'),
  countryPayTypes = $stepsWrap.data('country-paytypes'),
  $activeOperatorTitle = $('#activeOperatorTitle'),
  operatorPayTypes = $stepsWrap.data('operator-paytypes'),
  countryOffers = $stepsWrap.data('country-offers'),
  operatorOffers = $stepsWrap.data('operator-offers'),
  block_obj = $('.steps'),
  showType = $stepsWrap.data('showtype'),
  modalLocked = false;

$formStep2.on('afterValidate', function(event, messages, errors) {
  if (errors.length > 0) {
    window.promoStep.enableButtons();

    var errorMessages = [];

    $.each(messages, function(index, attribute) {
      $.each(attribute, function(index, message) {
        if (!!message) {
          errorMessages.push(message);
        }
      });
    });

    notifyInit(null, errorMessages.join(', '), false);
  }
});

$formStep2.on('beforeSubmit', function() {
  var data = $formStep2.serializeArray();
  var step1Data = $('#linkStep1Form').serializeArray();
  step1Data = step1Data.filter(function (item) {
    return item.name !== 'stepNumber';
  });
  data = data.concat(step1Data);
  data.push({name: 'submit', value: true});

  $.ajax({
    url: $formStep2.attr('action'),
    type: "POST",
    dataType: "json",
    data: data
  }).done(function(res) {
    if (res.success) {
      $linkIdInput.val(res.id);

      window.promoStep.nextStep();

      if ($isNewStream.val() == 1) {
        $isNewStream.val(0);
        var newStreamOption = $streamSelect.find('option.newStream');
        newStreamOption.val(res.stream_id);
        newStreamOption.removeClass('newStream');
        $streamSelect.val(res.stream_id);
        $streamSelect.selectpicker('refresh');
      }

      LINK_ID = res.id;
      LINK_NAME = res.link;
      LINK_LANDINGS_HAS_REVSHARE = JSON.stringify(res.landingsHasRevshare);
      LINK_LANDINGS_HAS_CPA = JSON.stringify(res.landingsHasCPA);

      var landingsHasRevshare = JSON.parse(LINK_LANDINGS_HAS_REVSHARE) || false;
      landingsHasRevshare ? $('#landings-has-revshare').removeClass('hidden') : $('#landings-has-revshare').addClass('hidden');
      var landingsHasCPA = JSON.parse(LINK_LANDINGS_HAS_CPA) || false;
      landingsHasCPA ? $('#landings-has-cpa').removeClass('hidden') : $('#landings-has-cpa').addClass('hidden');
    } else {
      $.each(res.errors, function(index, data) {
        $.each(data, function(index, data) {
          notifyInit(null, data, false);
        });
      });

      window.promoStep.enableButtons();
    }
  }).fail(function() {
    window.promoStep.enableButtons();
  });

  return false;
});

$('.selectpicker').selectpicker();
$ajaxContainerHeader.toggle(showType === 'table');
setTimeout(countryBarPosition.bind(null, block_obj), 20);
LandsFilter();

function countryBarPosition(block_obj) {
  blockObjWidth = block_obj.width() / 3;
  offset = block_obj.offset();

  countryBox.outerWidth(blockObjWidth);
}

/*
 * Редактирование лендов
 */
// Функция для открытия модального окна
function openModal(landingId, operatorId, selected, preOvelay) {
  if (modalLocked) {
    return;
  }

  modalLocked = true;
  if (!preOvelay) {
    $('.pre-overlay').fadeIn(300);
  }
  $.ajax({
    url: $stepsWrap.data('modal-action'),
    data: {
      landingId: landingId,
      operatorId: operatorId,
      linkId: $linkIdInput.val(),
      selected: selected,
    },
    dataType: 'html',
    type: 'get',
  }).done(function(result) {
    $landingModal.html(result).modal('show');
    $('.selectedLandingHiddenInput').each(function() {
      var $this = $(this);
     $('#L' + $this.data('landing-id') + 'O' +  $this.data('operator-id')).attr('checked',true);
    });
    $('.prev-landing, .next-landing').show()
    var $prevlanding = $('#lpid' + landingId);
    do {
      $prevlanding = $prevlanding.prev();
    } while ($prevlanding.length && $prevlanding.is(':hidden'));
    if ($prevlanding.length === 0) $('.prev-landing').hide();

    var $nextLanding = $('#lpid' + landingId);
    do {
      $nextLanding = $nextLanding.next();
    } while ($nextLanding.length && $nextLanding.is(':hidden'));
    if ($nextLanding.length === 0) $('.next-landing').hide();

    $('.pre-overlay').hide();
  }).complete(function() {
    modalLocked = false;
  });
}

//Открывем модал
$(document).on('click', '.open_lp_modal', function() {
  var $li = $(this).closest('li.item'),
    landingId = $li.data('landing-id'),
    operatorId = $li.data('operator-id'),
    selected = $('#selectedOperatorLanding-l' + landingId + 'o' + operatorId),
    selectedList = $('.selectedLanding' + landingId);

  openModal(landingId, operatorId, selectedList.length > 0 ? null : (selected.length > 0 ? selected.val() : null));
});

$(document).on('click', '.prev-landing, .next-landing', function () {
  var $modal = $(this).closest('.modal-dialog'),
    landingId = $modal.data('landing-id'),
    operatorId = $modal.data('operator-id'),
    selected = $('#selectedOperatorLanding-l' + landingId + 'o' + operatorId),
    selectedList = $('.selectedLanding' + landingId);

  var $landing = $('#lpid' + landingId);
  do {
    $landing = $(this).hasClass('prev-landing') ? $landing.prev() : $landing.next();
  } while ($landing.length && $landing.is(':hidden'));

  openModal($landing.data('landing-id'), operatorId, selectedList.length > 0 ? null : (selected.length > 0 ? selected.val() : null), true);
});

//Закрываем модалку, открываем Запросить доступ
$landingModal.on('click', '#modal_request_open', function() {
  var $this = $(this);
  if (modalLocked) {
    return;
  }
  modalLocked = true;

  $landingModal.modal('hide');

  setTimeout(function() {
    $.ajax({
      url: $stepsWrap.data('request-modal-action'),
      data: {
        landingId: $this.data('landing-id'),
        operatorId: $this.data('operator-id')
      },
      dataType: 'html',
      type: 'get',
    }).done(function(result) {
      $requestModal.html(result).modal('show');
      $(".filter-body .checkbox:not(.cb_g) input", $requestModal).each(function() {
        var label = $(this).siblings('label');

        if(label.text().length > 15) {
          label.wrapInner("<span data-toggle='tooltip' data-placement='top' title='"+label.text()+"'></span>");
          label.children().tooltip({container:'body'});
        }
      });
    }).complete(function() {
      modalLocked = false;
    });
  }, 600);
});

// Отсылаем реквест
$requestModal.on('click', '#landingRequestSubmitBt', function() {
  var $this = $(this),
    landingId = $this.data('landing-id'),
    operatorId = $this.data('operator-id'),
    combinedId = 'selectedOperatorLanding-l' + landingId + 'o' + operatorId,
    traffic_type = [];

  $requestModal.find('input[name=landingRequestType]:checked').each(function() {
    traffic_type.push($(this).val());
  });

  $.ajax({
    url: $stepsWrap.data('request-action'),
    type: 'post',
    data: {
      landing_id: landingId,
      description: $requestModal.find('#landingRequestDesc').val(),
      profit_type: $requestModal.find('input[name=profitType]:checked').val(),
      traffic_type: traffic_type,
    },
  }).done(function(res) {
    if(res.success) {
      if ($('#linkLandingModal').find('input[name=LandingOperator]:checked').length == 0) {
        selectLanding(landingId, operatorId, $requestModal.find('input[name=profitType]:checked').val());
      } else {
        $('.selectedLanding' + landingId).remove();
        $('#linkLandingModal').find('input[name=LandingOperator]:checked').each(
          function(index)  {
            var landingId = $(this).data('landing-id'), operatorId = $(this).data('operator-id');
            selectLanding(landingId, operatorId, $requestModal.find('input[name=profitType]:checked').val());
          }
        );
      }

      $requestModal.modal('hide');
      $ajaxContainerLandings.find('#lpid' + landingId).find('.landingSelectDisplay').removeClass('status__lock').addClass('status__wait');
      $ajaxContainerLandings.find('#lpid' + landingId).find('i').removeClass('open_lp_modal');
    } else {
      var errors = res.errors;
      if(errors != '') {
        $.each( errors, function( field, error ){
          $requestModal
            .find('.field-' + field)
            .addClass('has-error')
            .find('.help-block').html(error);
        });
      }
    }
  });
});

// Обработка кнопок "назад"
$landingModal.on('click', '.go_back', function() {
  $landingModal.modal('hide');
});

$requestModal.on('click', '.go_back', function() {
  $requestModal.modal('hide');

  setTimeout(function() {
    openModal($requestModal.find('#landingRequestId').val(), $('.addLinks__country-l3 li.active').data('operator-id'), null);
  }, 600);
});

// Выбираем лендинг
$(document).on('click', '.set_land_selected', function() {
  var $this = $(this),
    landingId = $this.data('landing-id'),
    operatorId = $this.data('operator-id'),
    combinedId = 'selectedOperatorLanding-l' + landingId + 'o' + operatorId,
    checked = $this.hasClass('selectedValueSwitch') ? '' : ':checked';

  // Cчетчик
  var current_land = $('#lpid' + landingId);

  var curr_operator = current_land.data('operator-id');

  var landingLockStatusElement = current_land.find('.addLinks__lands-img');
  if (landingLockStatusElement.hasClass('status__lock') && !landingLockStatusElement.hasClass('status__wait')) {
    openModal(landingId, operatorId, null);
  } else {

    if (($this.closest('.modal-content').find('input[name=LandingOperator]').length == 0 ||
      $this.closest('.modal-content').find('input[name=LandingOperator]' + checked).length != 0) &&
      ($this.closest('.modal-content').find('input[name=LandingOperator][data-operator-id=' + curr_operator + ']:checked').length !== 0
        || $this.closest('.modal-content').find('input[name=LandingOperator][data-operator-id=' + curr_operator + ']').length == 0
        || $('.addLinks__country-l2 .active .set_oss').hasClass('hide_oss_container')
      )) {

      current_land.find('.landingSelectDisplay.status__active').removeClass('status__active').addClass('status__selected');
      current_land.find('.landingSelectDisplay.status__lock').removeClass('status__lock').addClass('status__wait');

      if (current_land.find('.landingSelectDisplay').hasClass('status__wait')) {
        current_land.find('i').removeClass('open_lp_modal');
      }

      //Ставим отметки
      current_land.find('.selectedValueSwitch').removeClass('method-selected');
      current_land.find('.selectedValueSwitch.value-' + $this.data('profit-type')).addClass('method-selected');
    }

    var profitType = $this.data('profit-type');

    if ($this.closest('.modal-content').find('input[name=LandingOperator]').length === 0
      && typeof  $this.data('operator-ids') === 'undefined') {
      //добавление обычного ленда
      addHiddenInput(profitType, landingId, operatorId);
    } else {
      //выбор из грида ленда с одинаковыми операторами
      if ($this.data('operator-ids')) {
        var operatorIds = $this.data('operator-ids').toString().split(',');
        for (var i = 0; i < operatorIds.length; i++) {
          addHiddenInput(profitType, landingId, operatorIds[i]);
        }
      } else {
        //выбор из модалки ленда с одинаковыми операторами
        $this.closest('.modal-content').find('input[name=LandingOperator]').each(
          function (index) {
            var landingId = $(this).data('landing-id'), operatorId = $(this).data('operator-id');
            if ($(this).is(':checked')) {
              addHiddenInput(profitType, landingId, operatorId);
            } else {
              if ($('#selectedOperatorLanding-l' + landingId + 'o' + operatorId).length != 0) {
                $('#selectedOperatorLanding-l' + landingId + 'o' + operatorId).remove();
                //TODO считать количество выбранных лендов по оператору
                //выбранные оператор
                var current_oss = $allOss.filter('.oss-' + operatorId);
                //количество выбранных
                var count_selected = current_oss.find('i');
                //Увеличиваем счетчик выбранных
                if (parseInt(count_selected.html()) == 1) {
                  count_selected.remove();
                } else {
                  count_selected.html(parseInt(count_selected.html()) - 1);
                }
              }
            }
          }, profitType
        );
      }
    }

    $this.closest('.modal').modal('hide');
  }
});

function addHiddenInput(profitType, landingId, operatorId) {

  var combinedId = 'selectedOperatorLanding-l' + landingId + 'o' + operatorId;

  if ($('#' + combinedId).length === 0) {
    $('<input>').attr({
      type: 'hidden',
      id: combinedId,
      class: 'hidden-input selectedLandingHiddenInput selectedLanding' + landingId,
      name: 'LinkStep2Form[linkOperatorLandings][' + landingId + '][' + operatorId + '][profit_type]',
      'data-operator-id': operatorId,
      'data-landing-id': landingId,
      value: profitType
    }).appendTo($formStep2);

    //Выбранная страна
    var current_country = $allCountries.filter('.active');
    //заменяем иконку флага на селектед
    current_country.find('.set_oss').addClass('selected__c');

    //выбранные оператор
    var current_oss = $allOss.filter('.oss-' + operatorId);
    //количество выбранных
    var count_selected = current_oss.find('i');
    //Увеличиваем счетчик выбранных
    if (count_selected.length === 0) {
      current_oss.append('<i class="count__selected">1</i>');
    } else {
      count_selected.html(parseInt(count_selected.html()) + 1);
    }
  } else {
    $('#' + combinedId).val(profitType);
  }

}

// Убираем лендинг
$(document).on('click', '.status__selected .addLinks-img-overlay i, .status__wait .addLinks-img-overlay i, .col_land-name i.status__selected', function() {
  var $li = $(this).closest('li.item'),
    landingId = $li.data('landing-id'),
    operatorId = $li.data('operator-id'),
    combinedId = 'selectedOperatorLanding-l' + landingId + 'o' + operatorId;

  $li.find('.landingSelectDisplay.status__selected').
  removeClass('status__selected').addClass('status__active');

  $li.find('.landingSelectDisplay.status__wait').
  removeClass('status__wait').addClass('status__lock');

  if ($li.find('.landingSelectDisplay').hasClass('status__lock')) {
    $li.find('i').addClass('open_lp_modal');
  }

  // Убираем отметку с выбранного метод с лендинга
  $li.find('.selectedValueSwitch').removeClass('method-selected');

  //Выбранная страна
  var current_country = $allCountries.filter('.active');

  $('.selectedLanding' + landingId).each(function() {
    //выбранные оператор
    var current_oss = $allOss.filter('.oss-' + $(this).data('operator-id'));
    //количество выбранных
    var count_selected = current_oss.find('i');
    //Увеличиваем счетчик выбранных
    if (parseInt(count_selected.html()) == 1) {
      count_selected.remove();
      //заменяем иконку селектед на флаг
      current_country.find('.set_oss').removeClass('selected__c');
    } else {
      count_selected.html(parseInt(count_selected.html()) - 1);
    }
  });

  $('.selectedLanding' + landingId).remove();
});

// Переключение метода в гриде
$(document).on('click', '.grid > li .selectedValueSwitch', function() {
  var $this = $(this),
    $li = $this.closest('li.item'),
    landingHiddeninput = $('#selectedOperatorLanding-l' + $li.data('landing-id') + 'o' + $allOss.filter('.active').data('operator-id')),
    checkedLandingHiddeninputs= $('.selectedLanding' + $li.data('landing-id'));

  if (landingHiddeninput.length > 0) {
    landingHiddeninput.val($this.data('value'));
  }
  if (checkedLandingHiddeninputs.length > 0) {
    checkedLandingHiddeninputs.val($this.data('value'));
  }

  $li.find('.selectedValueSwitch').each(function() {
    var $value = $(this);
    if ($value.data('value') == $this.data('value')) {
      $value.addClass('method-selected');
    } else {
      $value.removeClass('method-selected');
    }
  });

});

/*
 * Фильтр
 */
//Сортируем по категориям
$categoryFilterItems.on('click', function(e) {
  e.preventDefault();
  var filterValue = $(this).attr('data-filter');
  animationFlag = true;
  isotopeInit();

  if ($(this).index() == 0) {
    $(this).addClass('selected__sing-cat').siblings().removeClass('selected__sing-cat');
    categoryArray = [filterValue];
  } else {
    $(this).siblings().eq(0).removeClass();
    $(this).toggleClass('selected__sing-cat');

    if ($(this).hasClass('selected__sing-cat')) {
      if (categoryArray.indexOf('*') > -1) {
        categoryArray.splice(categoryArray.indexOf('*'), 1);
      }
      categoryArray.push(filterValue);
    } else {
      categoryArray.splice(categoryArray.indexOf(filterValue), 1);
    }
  }

  animationFlag = false;
  $container.isotope({filter: buildFilterSelector()});
});

//Сортировка табличного грида
var column_sorting;
var asc = true;
$('body').on('click', '.isotope-sort', function() {
  var sortValue = $(this).data('sort');

  animationFlag = true;
  isotopeInit();

  if (column_sorting !== $(this).index()) {
    asc = false;
    column_sorting = $(this).index();
  }
  $container.isotope({sortBy: sortValue, sortAscending: asc});
  $('.isotope-sort').removeClass('sorting__desc sorting__ask');
  $(this).addClass(asc ? 'sorting__desc' : 'sorting__ask');
  asc = !asc;
});

// очистка фильтра
function clearSelection() {
  categoryArray = [];
  offerArray = [];
  paytypeArray = ['*'];
  $paytypesList.val([]);
  $categoryFilterItems.removeClass().eq(0).addClass('selected__sing-cat');
  $offerFilterItems.removeClass().eq(0).addClass('selected__sing-cat');
  $paytypesList.selectpicker('refresh');
}

// построение селектора по фильтру
function buildFilterSelector() {
  // если ни один не выбран
  if (categoryArray.length === 0 && paytypeArray.length === 0 && offerArray.length === 0) {
    return '';
  }

  // если выбран только тип оффера
  if (categoryArray.length === 0 && paytypeArray.length === 0) {
    return offerArray.join(', ');
  }
  // если выбран только тип оплаты
  if (categoryArray.length === 0 && offerArray.length === 0) {
    return paytypeArray.join(', ');
  }
  // если выбрана только категория лендов
  if (offerArray.length === 0 && paytypeArray.length === 0) {
    return categoryArray.join(', ');
  }

  // если не выбран тип оплаты
  if (paytypeArray.length === 0) {
    // если выбраны все
    if (offerArray.indexOf('*') > -1 && categoryArray.indexOf('*') > -1) {
      return '*';
    }

    if (offerArray.indexOf('*') > -1) {
      return categoryArray.join(', ');
    }

    if (categoryArray.indexOf('*') > -1) {
      return offerArray.join(', ');
    }

    return offerArray.map(function(offer) {
      return categoryArray.map(function(category) {
        return offer + category;
      }).join(', ');
    }).join(', ');
  }

  // если выбраны все офферы
  if (offerArray.indexOf('*') > -1) {
    // если выбраны все категории, отдаем типы оплат
    if (categoryArray.indexOf('*') > -1) {
      return paytypeArray.join(', ');
    }

    return paytypeArray.map(function(paytype) {
      return categoryArray.map(function(category) {
        return paytype + category;
      }).join(', ');
    }).join(', ');
  }

  // если выбраны все категории
  if (categoryArray.indexOf('*') > -1) {
    return paytypeArray.map(function(paytype) {
      return offerArray.map(function(offer) {
        return paytype + offer;
      }).join(', ');
    }).join(', ');
  }

  // иначе склеиваем все выбранные и отдаем
  return paytypeArray.map(function (type) {
    return offerArray.map(function (offer) {
      return categoryArray.map(function (category) {
        return offer + type + category;
      }).join(', ');
    }).join(', ');
  }).join(', ');
}

// Загрузка лендингов
function reloadContainer(operatorId) {
  $.ajax({
    url: $stepsWrap.data('list-action'),
    dataType: 'html',
    data: {
      operatorId: operatorId,
      linkId: $linkIdInput.val(),
    },
    type: 'post',
    success: function(data) {
      $(data).find('img').each(function() {
        $(this).load();
      });

      $ajaxContainerLandings.html(data);
      animationFlag = false;
      LandsFilter();
      reloadSelections();
    }
  });
}

//отмечаем лендинг выбранным
function selectLanding(landingId, operatorId, profitType)
{
  var combinedId = 'selectedOperatorLanding-l' + landingId + 'o' + operatorId,
    current_land = $('#lpid' + landingId);
  current_land.find('.selectedValueSwitch').removeClass('method-selected');
  current_land.find('.selectedValueSwitch.value-' + profitType).addClass('method-selected');

  if ($('#' + combinedId).length === 0) {
    $('<input>').attr({
      type: 'hidden',
      id: combinedId,
      class: 'hidden-input selectedLandingHiddenInput',
      name: 'LinkStep2Form[linkOperatorLandings][' + landingId + '][' + operatorId + '][profit_type]',
      'data-operator-id': operatorId,
      'data-landing-id': landingId,
      value: profitType
    }).appendTo($formStep2);
  }
}

// Обновляем лендинги, учитывая, что сейчас выбрано
function reloadSelections() {
  var currentOperator = $allOss.filter('.active').data('operator-id');

  $('.selectedLandingHiddenInput').each(function() {
    var $this = $(this);
    if ($this.data('operator-id') == currentOperator) {
      var $landing = $('#lpid' + $this.data('landing-id'));

      $landing.find('.landingSelectDisplay.status__active').
      removeClass('status__active').addClass('status__selected');

      $landing.find('.landingSelectDisplay.status__lock').
      removeClass('status__lock').addClass('status__wait');

      $landing.find('.landingSelectDisplay.status__blocked').
      removeClass('status__wait').addClass('status__lock');

      if ($landing.find('.landingSelectDisplay').hasClass('status__wait')) $landing.find('i.open_lp_modal').removeClass('open_lp_modal');

      // Устанавливаем класс на выбранный метод
      $landing.find('.selectedValueSwitch').removeClass('method-selected');
      $landing.find('.selectedValueSwitch.value-' + $this.val()).addClass('method-selected');
    }
  });
}

//Меняем грид лендов
$('.addLinks_grid').on('click', 'a', function(e) {
  e.preventDefault();
  var $this = $(this);
  showType = $this.data('grid');
  $this.addClass('active').siblings().removeClass('active');

  animationFlag = false;

  $ajaxContainerHeader.toggle(showType == 'table');
  $ajaxContainerLandings.toggleClass('grid_list', showType == 'table');
  LandsFilter();

  $.ajax({
    url: $stepsWrap.data('change-view-action'),
    data: {
      type: showType,
    },
    type: 'get'
  });
});

//Collapse стран
$('.country__collapse').on('click', function() {
  var $this = $(this);
  $('.addLinks__country-l2').not($this.next()).slideUp(0, function() {
    $this.next().slideDown(300);
  });
});

// Переключаем страну
function selectCountry($this) {
  $allCountries.removeClass('active');
  $this.addClass('active');

  var operatorWithSelectedLandings = $this.parent().siblings('.addLinks__country-l3').eq($this.index()).find('li:not(.hidden) i.count__selected').closest('li');

  if (operatorWithSelectedLandings.length > 0) {
    selectOSS(operatorWithSelectedLandings.eq(0));
  } else {
    selectOSS($this.parent().siblings('.addLinks__country-l3').eq($this.index()).find('li:not(.hidden):first'));
  }
}

// Выбор страны
$allCountries.on('click', '.set_oss', function() {
  selectCountry($(this).parent('li'));
});

// Переключаем ОСС
function selectOSS($this) {
  $allOss.removeClass('active');
  $allOss.parent('.addLinks__country-l3').removeClass('active');
  $this.addClass('active');
  $this.parent().addClass('active');
  $activeOperatorTitle.html($('.addLinks__country-l2 .active .set_oss').hasClass('hide_oss_container')
    ? ''
    : $this.data('operator-title'));
  reloadContainer($this.data('operator-id'));
}


//переключаем страны
$('body').on('click', '.set_oss', function() {
  $(this).parent('li').addClass('active').siblings('li').removeClass('active');

  var cont = $('.addLinks__country-pos');
  if ($(this).hasClass('hide_oss_container')) {
    cont.addClass('hide__oss');
    $timer = 305;
  } else {
    cont.removeClass('hide__oss');
    $timer = 190;
  }
  setTimeout(function() {
    if (typeof $container !== 'undefined') {
      $container.isotope({sortBy : ''});
    }
  }, $timer);
});

// Выбор ОСС
$allOss.on('click', function() {
  var $this = $(this);
  selectOSS($this);
});

// Меняем категорию офферов
$offerFilterItems.on('click', function() {
  var filterValue = $(this).attr('data-filter');

  if ($(this).index() === 0) {
    $(this).addClass('selected__sing-cat').siblings().removeClass('selected__sing-cat');
    offerArray = [filterValue];
  } else {
    $(this).siblings().eq(0).removeClass();
    $(this).toggleClass('selected__sing-cat');

    if ($(this).hasClass('selected__sing-cat')) {
      if (offerArray.indexOf('*') > -1) {
        offerArray.splice(offerArray.indexOf('*'), 1);
      }
      offerArray.push(filterValue);
    } else {
      offerArray.splice(offerArray.indexOf(filterValue), 1);
    }
  }

  // если сняли со всех категорий - ставим активной All
  if (!$offerFilterItems.hasClass('selected__sing-cat')) {
    var $firstOffer = $(this).siblings().eq(0);
    $firstOffer.addClass('selected__sing-cat');
    offerArray = [$firstOffer.attr('data-filter')];
  }

  var showPayTypes = false;
  var selectedOffers = [];

  $offerFilterItems.filter('.selected__sing-cat').each(function () {
    var offerId = $(this).data('id');
    if (offerId) {
      selectedOffers.push(offerId);
    }
    if ($(this).data('paytypes') == '1') {
      showPayTypes = true;
    }
  });

  if (showPayTypes) {
    $('.addLinks__header').removeClass('hide');
  } else {
    $paytypesList.selectpicker('deselectAll');
    paytypeArray = [];
    $('.addLinks__header').addClass('hide');
  }

  filterCountries(selectedOffers, $paytypesList.val());

  animationFlag = true;
  isotopeInit();

  var filterArr = buildFilterSelector();

  // скрываем категории, которые не должны выводится и поправляем фильтр
  categoryArray = [];

  $categoryFilterItems.each(function() {
    var $this = $(this);

    if ($this.data('filter') === '*' || $container.find($this.data('filter')).is(filterArr)) {
      $this.removeClass('hidden');
      if ($this.hasClass('selected__sing-cat')) {
        categoryArray.push($this.data('filter'));
      }
    } else {
      $this.addClass('hidden').removeClass('selected__sing-cat');
    }
  });

  $container.isotope({filter: filterArr});
});

// Меняем метод выплаты
$paytypesList.on('change', function() {
  var selectedPaytypesOptions = $(this).val();
  var selectedPaytypes;
  if (selectedPaytypesOptions instanceof Array && selectedPaytypesOptions.length < $paytypesListOptionsCount) {
    selectedPaytypes = selectedPaytypesOptions;
  } else {
    selectedPaytypes = [];
  }

  var selectedOffers = [];
  $offerFilterItems.filter('.selected__sing-cat').each(function () {
    var offerId = $(this).data('id');
    if (offerId) {
      selectedOffers.push(offerId);
    }
  });

  filterCountries(selectedOffers, selectedPaytypes);

  // Фильтруем лендинг
  paytypeArray = selectedPaytypes.map(function(val) {
    return '.paytype-' + val;
  });

  animationFlag = true;
  isotopeInit();
  $container.isotope({filter: buildFilterSelector()});
});

function filterCountries(offers, payTypes)
{
  // Убираем ненужные страны и ОСС
  $activeOperatorTitle.empty();

  $allCountries.removeClass('hidden');

  $allCountries.each(function() {
    var $this = $(this);
    var countryId = $this.data('country-id');
    var hasOffer = false;
    var hasPayType = false;

    if (offers && offers.length) {
      // если для этой страны вообще нет офееров
      if (!countryOffers.hasOwnProperty(countryId)) {
        $this.addClass('hidden');
        return;
      }

      // если для этой страны есть хоть одна выбранная категория офферов
      $.each(countryOffers[countryId], function (key, val1) {
        if (hasOffer === true) {
          return;
        }

        offers.forEach(function (val2) {
          if (val1 == val2) {
            hasOffer = true;
          }
        });
      });

      if (hasOffer === false) {
        $this.addClass('hidden');
        return;
      }
    }

    if (payTypes && payTypes.length) {
      // если для этой страны вообще нет типов оплат
      if (!countryPayTypes.hasOwnProperty(countryId)) {
        $this.addClass('hidden');
        return;
      }

      // если для этой страны есть хоть один выбранный тип оплаты
      $.each(countryPayTypes[countryId], function (key, val1) {
        if (hasPayType === true) {
          return;
        }

        payTypes.forEach(function (val2) {
          if (val1 == val2) {
            hasPayType = true;
          }
        });
      });

      if (hasPayType === false) {
        $this.addClass('hidden');
        return;
      }
    }
  });

  filterOss(offers, payTypes);

  $('.addLinks__country-l2').perfectScrollbar('update');

  // Не забываем выбирать подходящую страны и ОСС, если они сбросились
  var $activeCountries = $allCountries.filter('.active');
  if ($activeCountries.length === 0 || $activeCountries.is('.hidden')) {
    var filteredCountries = $allCountries.filter(':not(.hidden)'),
      countriesWithSelectedLandings = filteredCountries.filter(function() {
        var $this = $(this);
        return $this.parent().siblings('.addLinks__country-l3').eq($this.index()).find('li:not(.hidden) i.count__selected').length > 0;
      });

    selectCountry((countriesWithSelectedLandings.length > 0 ? countriesWithSelectedLandings : filteredCountries).filter(':first'));
  }

  var $activeOss = $allOss.filter('.active');
  if ($activeOss.length === 0 || $activeOss.is('.hidden')) {
    var $active = $allCountries.filter('.active');
    selectOSS($active.parent().siblings('.addLinks__country-l3').eq($active.index()).find('li:not(.hidden):first'));
  }

  $allCountries.each(function(){
    var $this = $(this);
    var length = $this.parent().siblings('.addLinks__country-l3').eq($this.index()).find('li:not(.hidden)').length;
    if (length === 1) {
      $this.find('.set_oss').hasClass('hide_oss_container');
      if ($this.hasClass('active')) {
        $this.parents('.addLinks__country-pos').addClass('hide__oss');
      }
    } else if (!$this.find('.set_oss').hasClass('hide_oss_permanently')) {
      $this.find('.set_oss').removeClass('hide_oss_container');
      if ($this.hasClass('active')) {
        $this.parents('.addLinks__country-pos').removeClass('hide__oss');
      }
    }
  });
}

function filterOss(offers, payTypes) {
  $allOss.removeClass('hidden');

  // if (!offers && !payTypes) {
  //   return;
  // }

  $allOss.each(function() {
    var $this = $(this);
    var operatorId = $this.data('operator-id');
    var hasOffer = false;
    var hasPayType = false;

    if (offers && offers.length) {
      // если для этой страны вообще нет офферов
      if (!operatorOffers.hasOwnProperty(operatorId)) {
        $this.addClass('hidden');
        return;
      }

      //
      $.each(operatorOffers[operatorId], function(key, val1) {
        if (hasOffer === true) {
          return;
        }

        offers.forEach(function (val2) {
          if (val1 == val2) {
            hasOffer = true;
          }
        });
      });

      if (hasOffer === false) {
        $this.addClass('hidden');
        return;
      }
    }

    if (payTypes && payTypes.length) {
      // если для этой страны вообще нет типов оплат
      if (!operatorPayTypes.hasOwnProperty(operatorId)) {
        $this.addClass('hidden');
        return;
      }

      //
      $.each(operatorPayTypes[operatorId], function (key, val1) {
        if (hasPayType === true) {
          return;
        }

        payTypes.forEach(function (val2) {
          if (val1 == val2) {
            hasPayType = true;
          }
        });
      });

      if (hasPayType === false) {
        $this.addClass('hidden');
        return;
      }
    }
  });
}

// подготовка изотопа
function isotopeInit() {
  isotope_options[showType].transitionDuration = animationFlag ? '0.6s' : '0s';
  $container.isotope(isotope_options[showType]);
}

// Фильтр лендингов
function LandsFilter() {
  if (isIsotopeActive) {
    $container.isotope('destroy');
  }

  $container = $('.grid');

  // скрываем категории, которые не должны выводится и поправляем фильтр
  categoryArray = [];
  offerArray = [];

  $offerFilterItems.filter('.selected__sing-cat').each(function() {
    var $this = $(this);
    offerArray.push($this.data('filter'));
  });

  // Если ничего не выбрано, выбираем все
  if (offerArray.length === 0) {
    $offerFilterItems.filter('[data-filter="*"]').addClass('selected__sing-cat');
    offerArray.push('*');
  }

  var filterArray = buildFilterSelector();

  $categoryFilterItems.each(function() {
    var $this = $(this);

    if (
      $this.data('filter') === '*'
      || ($container.children('li').is($this.data('filter')) && $container.find($this.data('filter')).is(filterArray))
    ) {
      $this.removeClass('hidden');
      if ($this.hasClass('selected__sing-cat')) {
        categoryArray.push($this.data('filter'));
      }
    } else {
      $this.addClass('hidden').removeClass('selected__sing-cat');
    }
  });

  // Если ничего не выбрано, выбираем все
  if (categoryArray.length === 0) {
    $categoryFilterItems.filter('[data-filter="*"]').addClass('selected__sing-cat');
    categoryArray.push('*');
  }

  // isotope
  isotopeInit();
  $container.isotope({filter: buildFilterSelector()});

  isIsotopeActive = true;
  animationFlag = true;
}

reloadSelections();
$allCountries.filter('.active').find('.set_oss').trigger('click');

$('.addLinks__country-l2').perfectScrollbar({
  suppressScrollX: true,
  scrollYMarginOffset: 10
});

// Скролл при переключении стран и операторов
blockObjWidth = block_obj.width() / 3;
offset = block_obj.offset();

// при скроле
$(window).scroll(function() {
  if ($(window).scrollTop() < offset.top + 78) {
    $(".addLinks__country").removeClass('addLinks__country_fixed');
  } else {
    $(".addLinks__country").addClass('addLinks__country_fixed');
  }
});

$(window).resize(function() {
  blockObjWidth = block_obj.width() / 3;
  $(".addLinks__country").outerWidth(blockObjWidth);
});

$('body').on('click', '.addLinks__country-l2 > li > span, .addLinks__country-l3 > li, .addLinks__country-l1 > li > span, .addLinks_grid a', function(e) {
  e.preventDefault();
  $('html, body').stop().animate({scrollTop: offset.top + 78}, 500)
});