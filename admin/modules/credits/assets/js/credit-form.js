var CreditForm = {

  formId: null,
  $form: null,
  $currencyInput: null,
  $amountInput: null,
  $calcSettingsBlock: null,

  creditSettings: null,

  init: function () {
    var context = this;
    this.$form = $('#' + this.formId);
    this.$currencyInput = $('#creditform-currency');
    this.$amountInput = $('#creditform-amount');
    this.$calcSettingsBlock = this.$form.find('.calc-settings-block');

    this.$currencyInput.on('change', function () {
      context.refreshPercentsBlock();
    });

    this.refreshPercentsBlock();

    this.$amountInput.on('keyup', function () {
      context.refreshAmountHint();
    });
  },

  refreshPercentsBlock: function () {
    // нет возможности посчитать процент
    if (!this.isDefaultPercentAvailable()) {
      this.$form.find('.calc-settings-warning').removeClass('hidden');
      this.$calcSettingsBlock.addClass('hidden');
      return;
    }

    this.$calcSettingsBlock.removeClass('hidden');
    this.$form.find('.calc-settings-warning').addClass('hidden');


    // обновляем глобальный процент
    this.$calcSettingsBlock.find('.credit-percent').find('span').html(rgk.formatter.asCurrency(this.getPercent()) + '%');
    // обновляем глобальный лимит
    this.$calcSettingsBlock.find('.credit-limit').find('span').html(rgk.formatter.asCurrency(this.getLimit(), this.$currencyInput.val()));
    this.refreshAmountHint();
  },

  refreshAmountHint: function () {
    var amount = this.$amountInput.val();
    var currency = this.$currencyInput.val();
    var percent = this.getPercent();
    if (!amount || !currency || isNaN(amount) || isNaN(percent) || amount <= 0 || percent < 0) {
      this.$form.find('.first-fee').addClass('hidden');
      this.$form.find('.receive-amount').addClass('hidden');
      return;
    }

    var value = percent * amount / 100;

    if (value > 99999999) {
      this.$form.find('.first-fee span')
        .html('too big value');
    } else {
      this.$form.find('.first-fee span')
        .html(rgk.formatter.asCurrency(value, currency));
      this.$form.find('.receive-amount span')
        .html(rgk.formatter.asCurrency(amount - value, currency));
    }

    this.$form.find('.first-fee').removeClass('hidden');
    this.$form.find('.receive-amount').removeClass('hidden');
  },

  isDefaultPercentAvailable: function () {
    return this.$currencyInput.val();
  },

  getPercent: function () {
    var currency = this.$currencyInput.val();
    var firstUpperCurrency = currency.charAt(0).toUpperCase() + currency.slice(1);
    return this.creditSettings['percent' + firstUpperCurrency];
  },

  getLimit: function () {
    var currency = this.$currencyInput.val();
    var firstUpperCurrency = currency.charAt(0).toUpperCase() + currency.slice(1);
    return this.creditSettings['limit' + firstUpperCurrency];
  }
};