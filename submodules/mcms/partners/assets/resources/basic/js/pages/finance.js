$(document).ajaxStart(function () {
  window.ajaxValidate = true;
}).ajaxComplete(function () {
  window.ajaxValidate = false;
});

var windowWidth = $(window).outerWidth();
$(window).resize(function () {
  windowWidth = $(window).outerWidth();
});

var financeModule = (function () {
  var $currencyRadio = $('.partners-currency > div >label:not(.disabled) input');
  var isCurrencyFirstChecked = false;
  var $currencyChangeAction = $('#currencyChangeAction');
  var $currencyChangeModal = $('#currencyChangeModal');


  $('.selectpicker').selectpicker();

  $currencyChangeModal.modal({
    backdrop: 'static',
    show: false
  });

  $('#change_theme').on('change', function (event) {
    event.stopPropagation();
    $('body').removeClass('cerulean blue amethyst alizarin orange green grey').addClass($(this).val());
  });

  $('.radio-buttons div >label:not(.disabled) input, .radio-buttons li:not(.disabled) input').on('change', function (e, flag) {
    if (!$(this).parent().hasClass('disabled')) {
      $(this).parent()
        .addClass('active')
        .parents('.radio-buttons')
        .find('label')
        .not($(this).parent())
        .removeClass('active');

      if ($(this).parents('.partners-currency').length && !flag) {
        scrollTop($('.profile'));
      }
    }
  });

  $currencyRadio.on('change', function (e, flag) {
    $('#newCurrency').val($(this).val());
    var cur = $currencyRadio.filter(":checked").val();
    if (!$(this).parent().hasClass('disabled')) {
      var currency = $(this).val();
      var $currencyRadioParent = $(this).parent();
      var $currencyRadioLabel = $currencyRadioParent.parents('.radio-buttons').find('label').not($(this).parent());

      // Показать / скрыть кнопку "Сменить валюту"
      if (CURRENT_CURRENCY == cur) {
        $currencyChangeAction.hide();
      } else {
        $currencyChangeAction.show();
      }

      $
        .ajax({
          url: SETTINGS_WALLET_TYPES_URL + currency
        })
        .done(function (response) {
          var data = response.data;
          var pdiv;

          if (data.showLocalFirst === true) {
            $('.local_wallets').removeClass('hidden');

            // ставим локальные перед глобальными
            pdiv = $("#local-p-s");
            if (pdiv.prev().attr('id') == 'international-p-s') {
              pdiv.insertBefore(pdiv.prev());
            }

          } else if (data.showLocal === true) {
            $('.local_wallets').removeClass('hidden');

            // ставим глобальные перед локальными
            pdiv = $("#international-p-s");
            if (pdiv.prev().attr('id') == 'local-p-s') {
              pdiv.insertBefore(pdiv.prev());
            }
          } else {
            $('.local_wallets').addClass('hidden');
          }
        });

      $currencyRadioParent.addClass('active')
        .removeClass('not-selected');
      $currencyRadioLabel.removeClass('active')
        .addClass('not-selected');

      isCurrencyFirstChecked = true;
    }
  });

  $currencyRadio.on('click', function () {
    if ($(this).parent().hasClass('disabled')) {
      return false;
    }
  });

  $currencyChangeAction.on('click', function (event) {
    if ($currencyChangeAction.hasClass('disabled')) {
      event.preventDefault();
      return false;
    }

    if ($currencyChangeAction.hasClass('change-currency')) {
      $.ajax({
        url: CHANGE_CURRENCY_URL,
        method: 'post',
        data: {newCurrency: $currencyRadio.filter(':checked').val()}
      });
      return false;
    }

    // Показать сумму после конверта
    $('#convertedBalance').html(CONVERT_BALANCE[$('#newCurrency').val()]);

    $currencyChangeModal.modal('show');
  });

  $('#acceptCurrencyChange, #convertCurrencyChange').on('click', function (e) {
    $currencyChangeModal.modal('hide');
    $currencyChangeAction.hide();
  });

  $('#cancelCurrencyChange').on('click', function () {
    $currencyChangeModal.modal('hide');
  });

  $currencyChangeModal.find('.close').on('click', function () {
    $currencyChangeModal.modal('hide');
  });

  $('.wire_tf-mode input').on('change', function () {
    $('.wire_tf').find('#' + $(this).val()).show().siblings().hide();
  });

  // $('.partners-currency label.active input').trigger('change', [true]);

  $('.partners-currency > div >label:not(.disabled) input:checked').trigger('change');
})();

/**
 * Управление платежными системами
 */
var PaysystemsList = {
  // TODO Использовать onchange, вместо onclick, при вызове метода showSettings в представлении, иначе метод вызывается два раза, что может сказаться на анимации скролла
  /**
   * Показать блок управления кошельками
   * TRICKY Кроме этого метода, при клике на ПС срабатывает хэндлер,
   * который добавляет класс и скролит в вверх. Не стал переносить в объект, так как тот код общий
   * @param paysystemId
   */
  showSettings: function (paysystemId) {
    if ($(this).hasClass('disabled')) return;

    this.hideSettingsHelp();
    this.hideSettingsAll();
    PaysystemWalletsList.show(paysystemId);
    PaysystemWalletForm.show(paysystemId);

    $('#paysystem-settings-' + paysystemId).show();
    PaysystemWalletsList.scrollToList(paysystemId);
  },
  /**
   * Скрыть блок управления кошельками
   * @param paysystemId
   */
  hideSettings: function (paysystemId) {
    $('#paysystem-settings-' + paysystemId).hide();
    PaysystemWalletsList.hide(paysystemId);
    PaysystemWalletForm.hide(paysystemId);
  },
  /**
   * Скрыть блок управления кошельками для всех ПС
   */
  hideSettingsAll: function () {
    var context = this;
    $('.js-paysystem-settings').each(function () {
      context.hideSettings($(this).data('paysystem-id'));
    });
  },
  /**
   * Скрыть блок описывающий настройку кошельков
   */
  hideSettingsHelp: function () {
    $('#paysystems-settings-help').hide();
  },
  /**
   * Обновить блок настроек ПС
   * @param paysystemId
   */
  reloadSettings: function (paysystemId) {
    $.pjax.reload($('#paysystem-settings-' + paysystemId), {
      url: PAYSYSTEM_SETTINGS_URL,
      push: false,
      replace: false,
      timeout: 10000
    });
  }
};

/**
 * Управление блоком списка кошельков
 */
var PaysystemWalletsList = {
  /**
   * Показать список кошельков
   * @param paysystemId
   */
  show: function (paysystemId) {
    this._getList(paysystemId).show();
  },
  /**
   * Скрыть список кошельков
   * @param paysystemId
   */
  hide: function (paysystemId) {
    this._getList(paysystemId).hide();
  },
  /**
   * Показать кнопку добавления кошелька
   * TRICKY Кнопки добавления может не быть, например если уже есть кошелек (возможно сгрупированный)
   * и ПС не позволяет создавать несколько кошельков на одну валюту
   * @param paysystemId
   */
  showAddButton: function (paysystemId) {
    this._getAddButton(paysystemId).show();
  },
  /**
   * Скрыть кнопку добавления кошелька
   * @param paysystemId
   * @see showAddButton()
   */
  hideAddButton: function (paysystemId) {
    this._getAddButton(paysystemId).hide();
  },
  /**
   * Отображена ли кнопка добавления кошелька
   * @param paysystemId
   * @return {boolean}
   */
  isAddButtonVisible: function (paysystemId) {
    return this._getAddButton(paysystemId).is(':visible');
  },
  /**
   * Показать все кнопки редактирования кошелька
   * @param paysystemId
   */
  showAllEditButtons: function (paysystemId) {
    $('#paysystem-wallets-' + paysystemId).find('.js-wallet-edit-button').each(function () {
      $(this).show();
    });
  },
  /**
   * Скрыть кнопку редактирования кошелька
   * @param walletId
   */
  hideEditButton: function (walletId) {
    var $editButton = $('#wallet-edit-button-' + walletId);
    $editButton.closest('.wallet_settings').find('.js-wallet-edit-button').each(function () {
      $(this).show();
    });
    $editButton.hide();
  },
  /**
   * Обновить список кошельков
   * @param paysystemId
   */
  reload: function (paysystemId) {
    var context = this;
    var $walletsList = this._getList(paysystemId);
    if ($walletsList.length === 0) return;

    // Отображение кнопки добавления кошелька после обновления списка, если она была видимой до обновления
    if (this.isAddButtonVisible(paysystemId)) {
      $walletsList.one('pjax:success', function () {
        context.showAddButton(paysystemId);
      });
    }

    // Обновление списка кошельков
    $.pjax.reload($walletsList, {
      url: WALLET_LIST_URL + paysystemId,
      push: false,
      replace: false,
      timeout: 10000
    });
  },
  scrollToList: function (paysystemId) {
    scrollTop(this._getList(paysystemId));
  },
  _getList: function (paysystemId) {
    return $('#paysystem-wallets-' + paysystemId);
  },
  _getAddButton: function (paysystemId) {
    return $('#card-addition-add-wrapper-' + paysystemId);
  }
};

/**
 * Управление блоком с формой кошелька
 */
var PaysystemWalletForm = {
  init: function () {
    // Действия после сохранения кошелька
    $(document).on('wallet_save_success', function (event, serverData) {
      if (typeof IS_SETTINGS_PAGE !== 'undefined') {
        PaysystemsList.reloadSettings(serverData.walletType);
      } else {
        scrollTop(); // Для страницы выплат
      }
    });

    // Конфирм пароля при !создании кошелька
    // Используется только при создании кошелька, так как при редактировании конфирм появляется еще до открытия формы
    $(document).on('beforeFormBlock', '.js-wallet-form', function (e) {
      // Если пароль не подтвержден: прерывает сабмит, отображает конфирм и после подтверждения пароля снова сабмитит форму
      var $form = $(this);
      var confirmCallback = function () {
        $form.submit();
      };

      if (!PasswordConfirm.check(confirmCallback, false)) return false;
    });
  },
  /**
   * Показать форму кошелька
   * @param paysystemId
   */
  show: function (paysystemId) {
    this._getForm(paysystemId).show();
  },
  /**
   * Скрыть форму кошелька
   * @param paysystemId
   */
  hide: function (paysystemId) {
    this._getForm(paysystemId).show();
  },
  /**
   * Скрыть и удалить форму кошелька.
   * @param paysystemId
   */
  remove: function (paysystemId) {
    PaysystemWalletsList.showAddButton(paysystemId);
    PaysystemWalletsList.showAllEditButtons(paysystemId);
    this.hide(paysystemId);
    this._getForm(paysystemId).html('');
  },
  /**
   * Загрузить форму добавления кошелька
   * @param paysystemId
   */
  loadAdd: function (paysystemId) {
    PaysystemWalletsList.hideAddButton(paysystemId);
    PaysystemWalletsList.showAllEditButtons(paysystemId);

    this._scrollToForm(paysystemId);
    this._load(paysystemId);
  },
  /**
   * Загрузить форму изменения кошелька
   * @param paysystemId
   * @param walletId
   */
  loadEdit: function (paysystemId, walletId) {
    var context = this;
    var confirmCallback = function () {
      context.loadEdit(paysystemId, walletId);
    };
    if (!PasswordConfirm.check(confirmCallback)) return;

    PaysystemWalletsList.showAddButton(paysystemId);
    PaysystemWalletsList.hideEditButton(walletId);

    this._scrollToForm(paysystemId);
    this._load(paysystemId, walletId);
  },
  /**
   * Загрузить форму
   * @param paysystemId
   * @param walletId
   * @private
   */
  _load: function (paysystemId, walletId) {
    var requestData = {'type': paysystemId};
    var context = this;

    if (walletId) requestData.walletId = walletId; else requestData.new = 1;

    $
      .ajax({
        url: FINANCE_PARAMETERS_WALLET_URL,
        data: requestData
      })
      .done(function (response) {
        context._getForm(paysystemId).html(response);
      });
  },
  _scrollToForm: function (paysystemId) {
    scrollTop(this._getForm(paysystemId));
  },
  _getForm: function (paysystemId) {
    return $('#paysystem-wallet-form-' + paysystemId);
  }
};

PaysystemWalletForm.init();

/**
 * Управление кошельками.
 * Именно отправка запросов на сервер, а не управление отображением
 */
var PaysystemWallets = {
  /**
   * Удалить кошелек/группу кошельков
   * @param paysystemId
   * @param walletId
   */
  remove: function (paysystemId, walletId) {
    $
      .post(DELETE_WALLET_URL + '?walletId=' + walletId)
      .done(function () {
        PaysystemsList.reloadSettings(paysystemId);
      });
  }
};

/**
 * Конфирм пароля
 */
var PasswordConfirm = {
  modal: $('#passwordConfirmModal'),
  /** @var {function} Колбэк для выполнения после успешного подтверждения пароля */
  callback: null,
  /** @var {boolean} Отображать алерт об успехе */
  showAlertSuccess: true,
  init: function () {
    var context = this;

    // Закрытие модалки с помощью крестика и святой воды
    this.modal.find('.close').on('click', function () {
      context._hide();
    });
  },
  /**
   * Проверить подтвержденность пароля.
   * Если пароль не подтвержден, отображается конфирм
   * @param {function} callback Функция для выполнения после подтверждения пароля
   * @param {boolean} showAlertSuccess По умолчанию true
   * @returns {boolean}
   */
  check: function (callback, showAlertSuccess) {
    this.callback = callback;
    this.showAlertSuccess = showAlertSuccess !== false;
    if (!this.isConfirmed()) this._show();

    return this.isConfirmed();
  },
  /**
   * Подтвержден ли пароль
   * @returns {boolean}
   */
  isConfirmed: function () {
    return IS_PASSWORD_CONFIRMED;
  },
  /**
   * Установить флаг "Пароль подтвержден"
   * @private
   */
  _setConfirmed: function () {
    if (this.isConfirmed()) return;

    IS_PASSWORD_CONFIRMED = true;
    this._hide();
    this.callback();
  },
  /**
   * Показать модалку для подтверждения пароля
   * @private
   */
  _show: function () {
    this.modal.modal('show');
  },
  /**
   * Скрыть модалку для подтверждения пароля
   * @private
   */
  _hide: function () {
    this.modal.modal('hide');
  }
};

PasswordConfirm.init();

/**
 * Скролл к элементу
 * @param {object} element Не обязательный параметр
 */
function scrollTop(element) {
  var
    currentPosition = $(window).scrollTop(),
    newPosition = element ? element.offset().top : 0;

  // Если новая позиция уже находится в первой половине окна, то скролл не нужен
  if (
    newPosition > currentPosition
    && newPosition < (currentPosition + window.innerHeight / 2)
  ) return;

  // Корректировка позиции с учетом фиксированной шапки на малых разрешениях
  if (windowWidth < 1000) newPosition = newPosition - 66;

  var
    stopAnimationEvents = 'mousedown keydown',
    stopAnimationHandler = function () {
      $('html, body').stop()
    };

  // http://stackoverflow.com/questions/16475198/jquery-scrolltop-animation
  $('html, body').animate({scrollTop: newPosition < 0 ? 0 : newPosition}, 500, null, function () {
    $(document).off(stopAnimationEvents, stopAnimationHandler);
  });
  $(document).one(stopAnimationEvents, stopAnimationHandler);
}


$(function () {

  var $localCaret = $('#local-wallets-title-caret'),
    $localTitle = $('#local-wallets-title'),
    localFirst = $localTitle.data('first'),
    $localWallets = $localTitle.nextAll('.local-wallets'),
    filterCookieKey = 'payments_local_wallets';

  function prepareFilter() {
    var filterCookie = Cookies.get(filterCookieKey);
    var transition = $.support.transition;
    $.support.transition = false;
    if (filterCookie === 'true') {
      showLocalWallets();
    } else if (typeof filterCookie === 'undefined' && localFirst) {
      showLocalWallets();
    } else {
      hideLocalWallets();
    }
    $.support.transition = transition;
  }

  function showLocalWallets() {
    $localWallets.show();
    $localCaret.addClass('caret').removeClass("caret-right");
  }

  function hideLocalWallets() {
    $localWallets.hide();
    $localCaret.removeClass('caret').addClass("caret-right");
  }

  function saveFilter() {
    Cookies.set(filterCookieKey, $localCaret.hasClass('caret') ? 'true' : false, {expires: 1});
  }

  $(document).on("click", '#local-wallets-title', function () {
    $(this).nextAll('.local-wallets').slideToggle(100);
    $localCaret.toggleClass('caret').toggleClass("caret-right");
    saveFilter();
  });

  prepareFilter();
});

