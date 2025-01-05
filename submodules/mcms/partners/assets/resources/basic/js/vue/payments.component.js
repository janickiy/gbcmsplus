Vue.config.devtools = window.isYiiDev;

/**
 * TRICKY Логика работы с min, max, limits описана в классе выплаты на пыхе
 */
var app = new Vue({
  el: '#payments',

  mixins: [VueFocus.mixin],

  data: {
    data: data,
    amount: '',
    error: false,
    dropdownOpen: false,
    showForm: false,
    isConvert: false,
    newPayments: [],
    amountFocused: false,
  },

  created: function () {
    this.amount = parseFloat(this.amount);

    var context = this;
    $.each(this.data.payment_systems, function () {
      context.modifyMinMaxPaysystem(this);
    });
  },

  methods: {
    // Знак числа
    octothorpe: function (num, useFormatter) {
      useFormatter = typeof useFormatter !== 'undefined' ? useFormatter : false;

      var number = parseFloat(num);
      number = useFormatter ? this.amountFormatter(number) : number;
      return parseFloat(number) > 0 ? '+' + number : number;
    },

    toggleDropdown: function () {
      if (this.amount >= this.minAmount && this.data.balance > 0) {
        this.error = false;
        this.dropdownOpen = !this.dropdownOpen;
      } else {
        this.error = true;
      }
    },

    // Добавляем платеж в список. Если ПС не активна - запрашиваем форму
    addPay: function (system) {
      if (system.wallets.length > 0) return;

      if (!system.active) {
        this.error = false;
        // TODO Логика дублируется
        $.ajax({
          url: ADD_SYSTEM_REQUEST + system.id,
          //url: ADD_SYSTEM_REQUEST,
          success: function (html) {
            $('#ajax__form').html(html);
            scrollTop($("#ajax__form"));
            this.showForm = true;
          }.bind(this)
        });
        return;
      }

      this.addWalletPay(system, system.wallets[0]);
      this.amount = this.maxValue;
    },

    addWalletPay: function (system, wallet) {
      if (!this.amount || !this.groupValid(system, wallet)) {
        this.error = true;
        this.showForm = false;
        return;
      }

      var icon = this.icon(wallet.currency);
      var isConvert = wallet.currency !== this.data.currency;
      if (isConvert) this.isConvert = isConvert;

      this.newPayments.push({
        system_id: system.id,
        wallet_id: wallet.id,
        amount: this.amount,
        currency: wallet.currency,
        name: system.name,
        icon: system.icon,
        iconConvert: icon,
        isConvert: isConvert,
        bonus: system.bonus,
        address: wallet.address,
        wallet_unique_value: wallet.uniqueValue
      });

      var walletUsed;
      if (wallet.isRelatedLimits && this.data.currency !== 'rub') {
        walletUsed = this.round(this.convertAmount(this.getAmountToCheckLimit(system, this.amount), wallet.currency));
      } else {
        walletUsed = this.amountBonus(this.convertAmount(this.getAmountToCheckLimit(system, this.amount), wallet.currency), 0);
      }

      wallet.usedDay = parseFloat(wallet.usedDay) + walletUsed;
      wallet.usedMonth = parseFloat(wallet.usedMonth) + walletUsed;

      if (wallet.isRelatedLimits) {
        system.groupWallets[wallet.uniqueValue].forEach(function (w) {
          if (w.id !== wallet.id && w.isRelatedLimits) {
            w.usedDay = parseFloat(w.usedDay) + walletUsed;
            w.usedMonth = parseFloat(w.usedMonth) + walletUsed;
          }
        });
      }

      this.showForm = false;
      this.dropdownOpen = false;
      this.amount = '';
      this.error = false;

      // Скрытие формы добавления кошелька
      $('#ajax__form').html('');
      this.showForm = false;

      scrollTop();
    },
    getAmountToCheckLimit: function (system, amount) {
      return amount + this.bonus(system, amount);
    },
    // Удаляем платеж со списка
    removePay: function (pay) {
      this.newPayments.splice(this.newPayments.indexOf(pay), 1);

      if (this.newPayments.length === 0) {
        this.isConvert = false;
      } else {
        var that = this;
        this.newPayments.forEach(function (pay) {
          if (pay.isConvert) {
            that.isConvert = true;
            return;
          }
          that.isConvert = false;
        });
      }

      this.updateWalletLimit(pay);
      this.amount = this.maxValue;

      this.amountFocused = true;
    },

    // Обновление лимита кошелька, при удалении из списка
    updateWalletLimit: function (pay) {
      var that = this;
      this.data.payment_systems.map(function (system) {
        if (pay.system_id === system.id) {
          system.wallets.map(function (wallet) {
            if (wallet.id === pay.wallet_id || wallet.id !== pay.wallet_id && wallet.isRelatedLimits && pay.wallet_unique_value === wallet.uniqueValue) {
              var walletUsed;
              if (wallet.isRelatedLimits && that.data.currency !== 'rub') {
                walletUsed = that.round(that.convertAmount(that.getAmountToCheckLimit(system, pay.amount), pay.currency));
              } else {
                walletUsed = that.amountBonus(that.convertAmount(that.getAmountToCheckLimit(system, pay.amount), pay.currency), 0);
              }
              wallet.usedDay = parseFloat(wallet.usedDay) - walletUsed;
              wallet.usedMonth = parseFloat(wallet.usedMonth) - walletUsed;
            }
          });
          return;
        }
      });
    },
    availableDayLimit: function (wallet) {
      if (wallet.dayLimit === false) return false;

      if (!wallet.monthLimit) {
        return wallet.dayLimit > 0 ? parseFloat(wallet.dayLimit) - parseFloat(wallet.usedDay) : 0;
      }

      if (parseFloat(wallet.monthLimit) < (parseFloat(wallet.usedMonth) + parseFloat(wallet.dayLimit) - parseFloat(wallet.usedDay))) {
        return wallet.monthLimit > 0 ? parseFloat(wallet.monthLimit) - parseFloat(wallet.usedMonth) : 0;
      }

      return wallet.dayLimit > 0 ? this.round(parseFloat(wallet.dayLimit) - parseFloat(wallet.usedDay)) : 0;
    },

    validateDayLimit: function (wallet, system) {
      var dayLimit = this.availableDayLimit(wallet);

      if (dayLimit === false) return true;
      var amount = this.convertAmount(this.amount, wallet.currency);
      return dayLimit >= this.floor(this.amountBonus(amount, this.bonus(system, amount), false));
    },

    validateRelatedLimits: function (relatedWallets, system) {
      var that = this;
      var isValid = relatedWallets.some(function (wallet) {
        return that.validateDayLimit(wallet, system);
      });

      return isValid;
    },

    validateMonthLimit: function (wallet, system) {
      if (wallet.monthLimit === false) return true;
      var amount = this.convertAmount(this.amount, wallet.currency);
      return wallet.monthLimit >= parseFloat(wallet.usedMonth) + this.floor(this.amountBonus(amount, this.bonus(system, amount), false));
    },

    round: function (sum) {
      return this._decimalOperation(sum, 2, 'round');
    },

    floor: function (sum) {
      return this._decimalOperation(sum, 2, 'floor');
    },

    ceil: function (sum) {
      return this._decimalOperation(sum, 2, 'ceil');
    },

    // https://habrahabr.ru/post/312880/#s2_3
    _decimalOperation: function (value, decimals, operation) {
      return Number(Math[operation](value + 'e' + decimals) + 'e-' + decimals);
    },

    groupValid: function (system, wallet) {
      var convertedAmountWithBonus = this.getConvertedAmountWithBonusByWallet(system, wallet);

      // Проверка нужна, чтобы валидация не проходила, если мин. лимит = 0
      if (convertedAmountWithBonus == 0) {
        return false;
      }

      var isMinValid = parseFloat(convertedAmountWithBonus) >= parseFloat(wallet.minClear);
      var isMaxValid = wallet.maxClear === false ? true : convertedAmountWithBonus <= wallet.maxClear;
      var isDayValid = this.validateDayLimit(wallet, system);
      var isMonthValid = this.validateMonthLimit(wallet, system);
      var isMaxValueValid = parseFloat(this.amount) <= parseFloat(this.maxValue);

      return isMinValid && isMaxValid && isDayValid && isMonthValid && isMaxValueValid;
    },

    getConvertedAmountWithBonusByWallet: function (system, wallet) {
      return this.getConvertedAmountWithBonusByValue(system, this.amount, wallet.currency);
    },

    getConvertedAmountWithBonusByValue: function (systemOrPay, amount, currency) {
      return this.amountBonus(this.convertAmount(amount, currency), this.convertAmount(this.bonus(systemOrPay, amount), currency));
    },

    getConvertedAmountWithBonusByPay: function (pay) {
      return this.getConvertedAmountWithBonusByValue(pay, pay.amount, pay.currency);
    },

    // Валидация введенной суммы
    validateAmount: function () {
      // заменяю запятые на точки, убираю все, ктоме точек и цифр, убираю лишние точки
      var stringAmount = this.amount.toString()
        .replace(/\,/g, ".")
        .replace(/[^\.0-9]/gim, '')
        .replace(/^([^\.]*\.)|\./g, '$1')
      ;

      var posDot = stringAmount.indexOf('.');       // проверяем, есть ли в строке точка
      if (posDot != -1) {                           // если точка есть
        if ((stringAmount.length - posDot) > 3) {   // проверяем, сколько знаков после точки, если больше 2 то
          stringAmount = stringAmount.slice(0, -1); // удаляем лишнее
        }
      }

      var floatAmount = parseFloat(stringAmount);

      if (!floatAmount || floatAmount < 0) {
        this.amount = '';
        return;
      }

      if (floatAmount >= this.minAmount) {
        this.error = false;
      }

      if (posDot > 0 || (stringAmount.slice(-1) === '0')) {
        // если точка - последний символ amount - строка (чтобы не потерять точку)
        // или последний символ 0, чтобы не потерять ноль если пользователь хочет ввести несколько символов после точки
        this.amount = stringAmount;
      }else{
        this.amount = floatAmount; // если точка - не последний символ amount - дробное число
      }
    },

    // Снятие фокуса с поля amount
    blurAmount: function () {
      this.amount = parseFloat(this.amount);
      this.amountFocused = false;
    },

    // Высчитываем процент бонуса
    bonus: function (systemOrPay, amount) {
      amount = isNaN(parseFloat(amount)) ? 0 : parseFloat(amount);
      return amount / 100 * parseFloat(systemOrPay.bonus);
    },

    // Модифицировать мин/макс с учетом процента
    // Например в админке ввели минималку 100 рублей и комиссию -3%
    // На фронте должно отобразиться минималка 103.09 рубля, что бы можно было вывести именно 100 рублей, а не 97
    // Если у ПС не комиссия, а профит, то не изменять лимит для красоты
    // То есть есть минималка 100, профит +3%, то минималка выводится не 97 рублей, а так же 100
    modifyMinMax: function (system, limit, type) {
      return this.modifyMinMaxByValues(limit, system.bonus, type);
    },

    // Модифицировать мин/макс с учетом процента используя значения
    modifyMinMaxByValues: function (amount, percent, type) {
      if (percent < 0 && amount) {
        return amount / (100 - Math.abs(percent)) * 100;
      } else if (percent > 0 && amount && type === 'max') {
        return amount / (100 + Math.abs(percent)) * 100;
      } else {
        // Для ПС с положительным процентом для минималки модификация не производится для красоты,
        // То есть в таком случае выводимая минималка - рекомендованная, настоящая минималка меньше
        return amount;
      }
    },

    modifyMin: function (system, limit) {
      return this.modifyMinMax(system, limit, 'min');
    },

    modifyMax: function (system, limit) {
      return limit ? this.modifyMinMax(system, limit, 'max') : false;
    },

    // Модифицировать лимиты ПС с учетом процента
    modifyMinMaxPaysystem: function (paysystem) {
      paysystem.min = this.modifyMin(paysystem, paysystem.min);
      paysystem.max = this.modifyMax(paysystem, paysystem.max);

      var context = this;
      $.each(paysystem.wallets, function (i, wallet) {
        paysystem.wallets[i] = context.modifyMinMaxWallet(paysystem, wallet);
      });

      return paysystem;
    },

    // Модифицировать лимиты кошелька с учетом процента
    modifyMinMaxWallet: function (paysystem, wallet) {
      wallet.minClear = wallet.min;
      wallet.maxClear = wallet.max;
      wallet.min = this.modifyMin(paysystem, wallet.min);
      wallet.max = this.modifyMax(paysystem, wallet.max);

      return wallet;
    },

    convert: function (fromCurrency, toCurrency) {
      if (fromCurrency === toCurrency) return 1;
      var convertType = fromCurrency + '_' + toCurrency;
      var courses = $.parseJSON(this.data.courses);
      return courses[convertType];
    },

    convertAmount: function (amount, currency, reverse) {
      reverse = reverse || false;
      currency = currency === false ? this.data.currency : currency;
      var percent = this.convert(this.data.currency, currency);
      return reverse ? amount / percent : percent * amount;
    },

    // Применяет бонус к сумме и округляет
    amountBonus: function (amount, bonus, round) {
      bonus = bonus || 0;
      amount = isNaN(parseFloat(amount)) ? 0 : parseFloat(amount);
      round = (round !== false);
      var result = parseFloat(amount) + parseFloat(bonus);

      return round ? this.round(result) : result;
    },

    // Округление и форматирование
    amountFormatter: function (amount) {
      amount = this.round(parseFloat(amount))
        .toString()
        .replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1' + ' ')
        .replace('.', ',');

      return amount;
    },

    // Активируем ПС с id = id и прячем форму
    setActiveSystem: function (id, wallet) {
      var thisApp = this;
      this.data.payment_systems.map(function (system) {
        if (system.id == id) {
          system.active = true;
          wallet = thisApp.modifyMinMaxWallet(system, wallet);
          system.wallets.push(wallet);

          // Если min нового кошелька меньше чем у ПС, то присваиваем его для ПС.
          // Причем min кошелька конвертим в валюту партнера.
          // То же самое для max
          var convertedMax = thisApp.floor(thisApp.convertAmount(wallet.max, wallet.currency, true));
          if (convertedMax != 0) {
            if (system.max == 0 || convertedMax > system.max) system.max = convertedMax;
          }
          var convertedMin = thisApp.ceil(thisApp.convertAmount(wallet.min, wallet.currency, true));
          if (convertedMin != 0) {
            if (system.min == 0 || convertedMin < system.min) system.min = convertedMin;
          }
          if (system.activeCurrency.indexOf(wallet.currency) === -1) {
            system.activeCurrency.push(wallet.currency);
          }
        }
      });
      this.showForm = false;
    },

    // Класс с цветом для бонуса
    paymentsBonus: function (bonus) {
      return {
        'payments_bonus-plus': bonus > 0,
        'payments_bonus-minus': bonus < 0
      }
    },

    walletError: function (wallet, system) {
      return {
        'has-error': !this.groupValid(system, wallet)
      }
    },

    // Проверка кошельков ПС на ошибки
    // true - если все кошельки не валидны
    // false - если хотя бы один из кошельков валиден
    paysystemError: function (system, currency) {
      var context = this,
        hasErrors = null;

      if (system.activeCurrency.indexOf(currency) !== -1) {
        var ar = system.wallets.filter(function (wallet) {
          return wallet.currency === currency;
        });
        hasErrors = !ar.some(function (wallet) {
          return context.groupValid(system, wallet);
        });
      }

      return hasErrors;
    },

    // Иконка валюты
    icon: function (currency) {
      currency = currency === false ? this.data.currency : currency;
      switch (currency) {
        case RUR:
          return "<i class='icon-ruble'></i>";
          break;
        case EUR:
          return "<i class='icon-euro'></i>";
          break;
        case USD:
          return "$";
          break;
      }
    },

    iconsArray: function (wallets) {
      var icons = '';
      var that = this;
      wallets.forEach(function (wallet) {
        icons += that.icon(wallet.currency);
      });

      return icons;
    },

    currencyChar: function (currency) {
      currency = currency === false ? this.data.currency : currency;
      switch (currency) {
        case RUR:
          return "₽";
          break;
        case EUR:
          return "€";
          break;
        case USD:
          return "$";
          break;
      }
    },

    setAmount: function (val) {
      this.amount = val;
    }
  },

  computed: {
    // Максимальный баланс для вывода
    balance: function () {
      return this.data.balance;
    },
    max_limit: function () {
      // Определение максимального лимита на выплату (с учетом дневных и месячных лимитов)
      var activePaymentSystems = this.data.payment_systems.filter(function (obj) {
        return obj.active;
      });

      var max = 0;
      var maxSum = 0;
      var maxSumClear = 0;
      var minSum = 0;
      for (i = 0; i < activePaymentSystems.length; ++i) {
        for (j = 0; j < activePaymentSystems[i].wallets.length; j++) {
          if (!activePaymentSystems[i].wallets[j].max
            && !activePaymentSystems[i].wallets[j].dayLimit
            && !activePaymentSystems[i].wallets[j].monthLimit) {
            // существует кошелек без лимитов
            return this.balance;
          }

          // проверяем максимальную выплату
          maxSum = parseFloat(activePaymentSystems[i].wallets[j].max);
          maxSumClear = parseFloat(activePaymentSystems[i].wallets[j].maxClear);
          minSum = parseFloat(activePaymentSystems[i].wallets[j].min);

          // сравниваем с дневным лимитом
          if (activePaymentSystems[i].wallets[j].dayLimit) {
            var dayLimit = parseFloat(activePaymentSystems[i].wallets[j].dayLimit) - parseFloat(activePaymentSystems[i].wallets[j].usedDay);
            maxSum = dayLimit < maxSumClear ? dayLimit : maxSum;
          }

          // сравниваем с месячным лимитом
          if (activePaymentSystems[i].wallets[j].monthLimit) {
            var monthLimit = parseFloat(activePaymentSystems[i].wallets[j].monthLimit) - parseFloat(activePaymentSystems[i].wallets[j].usedMonth);
            maxSum = monthLimit < maxSumClear ? monthLimit : maxSum;
          }

          maxSum = this.convertAmount(maxSum, activePaymentSystems[i].wallets[j].currency, true);

          if (maxSum > max && this.currentBalance >= minSum) max = maxSum;
        }
      }

      max = max <= 0 ? 0 : max;
      return this.amountBonus(this.floor(max), 0);
    },

    payment_systems: function () {
      this.data.payment_systems.forEach(function (ps) {
        ps.groupWallets = ps.wallets.reduce(function (pv, wallet) {
          (pv[wallet.uniqueValue] = pv[wallet.uniqueValue] || []).push(wallet);
          return pv;
        }, {});
      });

      var localPayments = this.data.payment_systems.filter(function (obj) {
        return obj.local;
      });
      var localActivePayments = localPayments.filter(function (obj) {
        return obj.active;
      });
      var localNoactivePayments = localPayments.filter(function (obj) {
        return !obj.active;
      });

      var internationalPayments = this.data.payment_systems.filter(function (obj) {
        return !obj.local;
      });
      var internationalActivePayments = internationalPayments.filter(function (obj) {
        return obj.active;
      });
      var internationalNoactivePayments = internationalPayments.filter(function (obj) {
        return !obj.active;
      });

      return {
        local: [].concat(
          sortDesc(localActivePayments, 'bonus'),
          sortDesc(localNoactivePayments, 'bonus')
        ),
        international: [].concat(
          sortDesc(internationalActivePayments, 'bonus'),
          sortDesc(internationalNoactivePayments, 'bonus')
        )
      };
    },

    // Сумма всех заявок на выплату
    paySum: function () {
      var sum = this.newPayments.reduce(function (a, b) {
        return a + b.amount;
      }, 0);
      return this.round(sum);
    },
    // Текущий баланс
    currentBalance: function () {
      return this.newPayments.length
        ? this.round(this.balance - this.paySum)
        : this.balance;
    },
    // Максимальный остаток, для которого можно добавить платеж
    maxValue: function () {
      var balance = this.currentBalance;
      var max_limit = this.max_limit;

      return max_limit > balance ? balance : max_limit;
    },

    setMaxAmountWithBonus: function () {
      this.amount = parseFloat(this.maxValueWithBonus);
    },

    minAmount: function () {
      var activePayments = this.data.payment_systems.filter(function (obj) {
        return obj.active;
      });
      var min = activePayments.length ? activePayments[0].min : 0;
      activePayments.forEach(function (ps) {
        if (ps.min < min) min = ps.min;
      });
      return min;
    },
    maxBonus: function () {
      var activePayments = this.data.payment_systems.filter(function (obj) {
        return obj.active;
      });
      var bonus = activePayments.length ? parseFloat(activePayments[0].bonus) : 0;
      activePayments.forEach(function (ps) {
        if (parseFloat(ps.bonus) > bonus) bonus = parseFloat(ps.bonus);
      });
      return bonus;
    },

    maxValueWithBonus: function () {
      return this.maxValue;
    }
  },

  watch: {
    // Следим за переменной showForm. Если true - показываем форму, иначе прячем форму, и показываем баланс
    showForm: function (val) {
      if (val) {
        $('.createPaymentSystem').show();
        // $('.balanceCol').hide();
      } else {
        $('.createPaymentSystem').hide();
        // $('.balanceCol').show();
      }
    },

    amount: function (val) {
      this.validateAmount(val);
    }
  }
});

Vue.component('payment-item', {
  template: '#paymentItemTemplate',
  props: ['system', 'amount', 'local', 'appCurrency'],
  data: function () {
    return {
      openWallets: false
    }
  },
  methods: {
    paymentClick: function () {
      this.openWallets = !this.openWallets;
      //Если один кошелек и одна валюта
      if (Object.keys(this.system.groupWallets).length === 1 && this.system.wallets.length === 1) {
        this.$root.addWalletPay(this.system, this.system.wallets[0]);
      }
    },
    systemBonusToInt: function (bonus) {
      var intBonus = +bonus;
      if (isNaN(intBonus)) {
        return 0;
      } else {
        return intBonus;
      }
    },
    paymentClasses: function () {
      var isCursor = Object.keys(this.system.groupWallets).length > 1
        || Object.keys(this.system.groupWallets).length === 1 && this.system.wallets.length === 1;
      return {
        'open': this.openWallets,
        'cursor': isCursor
      }
    }
  }
});

function sortAsc(array, prop) {
  return array.sort(function (a, b) {
    var c = parseFloat(a[prop]);
    var d = parseFloat(b[prop]);
    return (d < c) - (c < d);
  })
}

function sortDesc(array, prop) {
  return array.sort(function (a, b) {
    var c = parseFloat(a[prop]);
    var d = parseFloat(b[prop]);
    return (c < d) - (d < c);
  })
}

$('body').on('click', function (event) {
  if ($(event.target).closest(".payments__type").length || $(event.target).closest(".payments-dropdown").length) return;
  app.dropdownOpen = false;
});