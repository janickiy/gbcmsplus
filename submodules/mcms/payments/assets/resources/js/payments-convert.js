/**
 * Подтверждение конверта при создании выплаты
 */
paymentConfirmConvert = {
  /** @var {object} Информация о выплате */
  paymentInfo: null,
  /** @var {boolean} Конвертация подтверждена */
  convertConfirmed: false,

  $userPaymentForm: $('#userpayment-form'),
  $userPaymentWallet: $('#userpaymentform-user_wallet_id'),
  $userPaymentAmount: $('#userpaymentform-invoice_amount'),

  /**
   * Обновление информации о выплате
   * @param {object} data Данные из actionUserDetail()
   * @see \mcms\payments\controllers\PaymentsController::actionUserDetail()
   */
  updatePaymentInfo: function (data) {
    if (!data) return false;

    this.paymentInfo = {
      wallet: {currency: data.wallet.currency},
      balance: {currency: data.balance.currency},
      convertCourse: data.convertCourse
    };
  },

  /**
   * Генерация текста конфирма
   * @return {string}
   */
  generateText: function () {
    var
      amount = this.$userPaymentAmount.val(),
      amountConverted = amount * this.paymentInfo.convertCourse;
    amountConverted = Math.round(amountConverted * 100) / 100;

    return this.$userPaymentForm.data('convert-confirm-text') + ' ' + amountConverted + ' '
      + this.paymentInfo.wallet.currency.toUpperCase();
  },

  /**
   * Конфирм конвертации.
   * Сценарий работы:
   * - по умолчанию convertConfirmed = false
   * - после нажатия на сабмит вызывается этот метод, отправка формы отменяется и отображается конфирм
   * - если в конфирме нажали отмену, ничего не происходит, convertConfirmed остается false и сценарий повторяется
   * - если в конфирме нажали подтверждение, форма снова сабмитится, снова вызывается этот метод для проверки convertConfirmed
   * и так как convertConfirmed = true, форма отправляется как и обычно
   * @return {boolean}
   * true - конвертация не требуется, подтверждена или требуется валидация формы
   * false - конвертация отклонена пользователем
   */
  confirm: function () {
    if (!this.$userPaymentForm.data('is-new-record')) return true;

    if (this.$userPaymentWallet.val() && !this.paymentInfo) {
      alert(this.$userPaymentForm.data('payment-info-error'));
      return false;
    }

    var amount = this.$userPaymentAmount.val();
    // Сумма выплаты не указана. Отправление формы на валидацию
    if (!amount) return true;

    // Выплата не требует конвертации
    if (this.paymentInfo.balance.currency === this.paymentInfo.wallet.currency) return true;

    var context = this;
    if (!this.convertConfirmed) {
      yii.confirm(this.generateText(), function () {
        context.convertConfirmed = true;
        context.$userPaymentForm.trigger('submit');
      });
    }

    return this.convertConfirmed;
  }
};
