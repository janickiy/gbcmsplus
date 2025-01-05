<?php

use mcms\common\form\AjaxActiveForm;
use mcms\common\helpers\Html;
use mcms\partners\components\widgets\PriceWidget;
use mcms\partners\models\EarlyPaymentRequestForm;
use mcms\payments\models\PartnerPaymentSettings;
use yii\helpers\Json;
use yii\helpers\Url;

// TODO В этом представлении есть много проверок на отрицательный баланс. Если баланс отрицательный, то отображается 0.
// Это разве нормально?

/** @var \yii\data\ActiveDataProvider $invoices */
/** @var \mcms\payments\components\UserBalance $currentBalance */
/** @var \mcms\payments\components\UserBalance $oldBalance */
/** @var \yii\data\ActiveDataProvider $payments */
/** @var EarlyPaymentRequestForm $paymentForm */
/** @var array[] $paymentSystems */
/** @var array[] $courses */
/** @var \yii\web\View $this */
/** @var boolean $hasOldBalance */
/** @var boolean $hasOldPayments */
/** @var boolean $showLocal */
/** @var boolean $showLocalFirst */
/** @var double $convertedSum */
/** @var \mcms\payments\components\UserBalance $fixBalance */
/** @var bool $hasWalletDetailsAccess */
/** @var PartnerPaymentSettings $partnerPaymentSettings */
/** @var \mcms\payments\models\UserWallet[] $userWallets */

// Мигать ошибками при клике на кошелек
$this->registerJs(<<<JS
$(document).on('click', '.wallet', function() {
  $(this).parents('li:first').find('.payment .error').effect("highlight", {color: "#EEBFBF"});
  $(this).find('.error').effect("highlight", {color: "#EEBFBF"});
});
$(document).on('click', '.payment', function() {
  $(this).find('.error, .has-error').effect("highlight", {color: "#EEBFBF"});
});
JS
);

// Активация ПС после добавления кошелька
// TRICKY Не ЗАБЫВАЕМ СЮДА ДОБАВИТЬ НОВЫЕ ПАРАМЕТРЫ КОШЕЛЬКОВ ЕСЛИ ОНИ ПОЯВЛЯЮТСЯ В PaymentsController::actionBalance()
$this->registerJs(/** @lang JavaScript */
  '$(document).on("wallet_save_success", function(event, serverData) {
      $.each(serverData.wallets, function(index, data) {
        app.setActiveSystem(data.walletType, {
          id: data.walletId,
          address: data.walletUniqueValue,
          usedDay: 0,
          usedMonth: 0,
          currency: data.currency,
          dayLimit: data.dayLimit,
          monthLimit: data.monthLimit,
          min: data.min,
          max: data.max,
          isRelatedLimits: data.isRelatedLimits
        });
      });
  $("#ajax__form").html("");
});');
?>
  <div class="col-xs-5 pull-right payments-balance-col">
    <div class="bgf balanceCol">
      <div class="title"><h2><?= Yii::_t('payments.payments-balance') ?></h2></div>
      <div class="payments content__position">
        <div class="payments-balance_total text-center">
          <?php if ($hasOldBalance): ?>
            <span style="display:block">
                <?= PriceWidget::widget([
                  'value' => $currentBalance->getBalance(),
                  'currency' => $currentBalance->getCurrency(),
                ]) ?>
              </span>

            <?= Html::a(
              '<span class="balance-light">' .
              PriceWidget::widget([
                'value' => $oldBalance->getBalance(),
                'currency' => $oldBalance->getCurrency(),
              ]) .
              '</span>' .
              '&nbsp;→&nbsp;' . PriceWidget::widget([
                'value' => $convertedSum,
                'currency' => $currentBalance->getCurrency(),
              ]),
              ['convert-balance'],
              [
                'data-confirm' => Yii::_t('payments.convert_confirm'),
                'title' => Yii::_t('payments.convert_to_new_currency'), // todo
              ]
            ) ?>
          <?php else: ?>
            <span>
                <?= PriceWidget::widget([
                  'value' => $currentBalance->getBalance(),
                  'currency' => $currentBalance->getCurrency(),
                ]) ?>
              </span>
          <?php endif; ?>
        </div>
      </div>
      <div class="payments payments__position">
        <div class="row text-center">
          <div class="col-xs-6">
            <div class="payments-balance payments-balance-green">
                <span>
                  <?= PriceWidget::widget([
                    'value' => ($mainBalance = $currentBalance->getMain()) < 0 ? 0 : $mainBalance,
                    'currency' => $currentBalance->getCurrency(),
                  ]) ?>
                </span>
              <small><?= Yii::_t('payments.payments-label-to-payment') ?></small>
            </div>
          </div>
          <div class="col-xs-6">
            <div class="payments-balance">
                <span>
                  <?= PriceWidget::widget([
                    'value' => $currentBalance->getHold(),
                    'currency' => $currentBalance->getCurrency(),
                  ]) ?>
                </span>
              <small><?= Yii::_t('payments.payments-label-in-hold') ?></small>
            </div>
          </div>
        </div>
      </div>
      <div class="content__position danger_message hidden-xs">
        <?php if ($hasOldBalance): ?>
          <i class="icon-change"></i>
          <p><?= Yii::_t('partners.payments.changed-currency-text') ?></p>
        <?php else: ?>
          <i class="icon-ok_comment"></i>
          <p><?= Yii::_t(
              'partners.payments.multiple_payments_text',
              ['link' => Html::a(Yii::_t('partners.payments.multiple_payments_text_settings'), ['payments/settings'])]
            ) ?></p>
        <?php endif; ?>
      </div>
      <?php // TODO Выпилить автовыплаты ?>
      <?php /* <div class="payments content__position">
            <div class="payments-date">
              <i class="icon-calendar"></i>
              <span><?= Yii::_t('payments.payments-payment-date') ?>:
                <?= Yii::$app->formatter->asDate($paymentNextDate, 'long') ?>
          </span>
            </div>

            <div class="payments-status <?= $isAutoPayments ? 'on' : '' ?>">
              <i class="icon-profile"></i>
              <span><?= Yii::_t('payments.payments-label-payment-status') ?>: <i
                  data-on="<?= Yii::_t('payments.payments-label-auto') ?>"
                  data-off="<?= Yii::_t('payments.payments-label-manual') ?>"></i></span>
              <i
                data-toggle="tooltip"
                data-placement="top" title=""
                class="icon-question"
                data-original-title="<?= Yii::_t('payments.payments-message-auto-payment-condition', [$autopayEnabledSubscriptionCount]) ?>"
              ></i>
            </div>
          </div>
          */ ?>
      <?php
      
      $isAutoPayments = !$partnerPaymentSettings->isNewRecord;
      $isAutopayAvailable = true;
      ?>
      <?= $this->render('_autopayments_toggle', compact('isAutoPayments', 'isAutopayAvailable')) ?>
      <?= $this->render('_auto_payment_form',['model'=> $partnerPaymentSettings,'userWallets'=>$userWallets])?>
      <?php  //<?= $this->render('_payment_form', compact('paymentForm', 'isAutoPayments', 'canRequirePayments', 'balance', 'walletAccount')) ?>
    </div>
    <div id="ajax__form" class="bgf profile profile-finance"></div>
  </div>

  <div class="col-xs-7 payments-table-col">
    <div class="bgf" id="payments">
      <div class="title">
        <h2><?= Yii::_t('partners.payments.payments_request') ?></h2>
      </div>
      <div class="row payments_input_box">
        <div class="col-xs-6">
          <div class="form-group amount-input" v-bind:class="{'has-error' : error}">
            <label for="amount"><?= Yii::_t('partners.payments.payment_request_sum') ?></label>
            <input type="text"
                   id="amount"
                   class="form-control"
                   v-bind:value="setMaxAmountWithBonus"
                   v-on:keyup="validateAmount"
                   v-on:blur="blurAmount"
                   v-focus="amountFocused" @focus="amountFocused = true"
                   v-bind:placeholder="maxValue >= minAmount ? '<?= Yii::_t('partners.payments.from') ?> ' + amountFormatter(minAmount) +' <?= Yii::_t('partners.payments.to') ?> ' + amountFormatter(maxValueWithBonus) + ' ' + currencyChar(false): ''"
                   v-model="amount">
          </div>
        </div>
      </div>
      <div
        class="payments content__position"
        v-bind:class="{ 'open' : dropdownOpen }"
      >
        <ul class="payments-dropdown">
          <template v-if="payment_systems.local.length > 0 && data.showLocalFirst">
            <li class="group" id="local-wallets-title" data-first="true">
              <?= Yii::_t('partners.payments.local_payment_methods') ?>
              <span id="local-wallets-title-caret"></span>
            </li>
            <li
              class="local-wallets"
              is="payment-item"
              v-for="system in payment_systems.local"
              v-bind:system="system"
              v-bind:amount="amount"
              v-bind:local="true"
              v-bind:app-currency="data.currency"
              v-bind:class="{ 'gray' : !system.active }"
              v-cloak>
            </li>
          </template>

          <template v-if="payment_systems.international.length > 0 && data.showLocal">
            <li class="group"><?= Yii::_t('partners.payments.worldwide_payments_methods') ?></li>
          </template>
          <li
            is="payment-item"
            v-for="system in payment_systems.international"
            v-bind:system="system"
            v-bind:amount="amount"
            v-bind:local="false"
            v-bind:app-currency="data.currency"
            v-bind:class="{ 'gray' : !system.active }"
            v-cloak>
          </li>

          <template v-if="payment_systems.local.length > 0 && data.showLocal && !data.showLocalFirst">
            <li class="group" id="local-wallets-title" data-first="false">
              <?= Yii::_t('partners.payments.local_payment_methods') ?>
              <span id="local-wallets-title-caret"></span>
            </li>
            <li
              class="local-wallets"
              is="payment-item"
              v-for="system in payment_systems.local"
              v-bind:system="system"
              v-bind:amount="amount"
              v-bind:local="true"
              v-bind:app-currency="data.currency"
              v-bind:class="{ 'gray' : !system.active }"
              v-cloak>
            </li>
          </template>
        </ul>
      </div>
      <div
        class="sendpayments-form-wrapper"
        v-show="newPayments.length > 0"
      >
        <?php AjaxActiveForm::begin(['id' => 'sendPayments', 'action' => ['request-early-payment'], 'method' => 'post']) ?>
        <ul class="payments__checked">
          <li v-for="(pay, index) in newPayments" v-cloak>
            <input type="hidden"
                   v-bind:name="'<?= $paymentForm->formName() ?>[payments]['+ index +'][wallet_type]'"
                   v-model="pay.system_id">
            <input type="hidden"
                   v-bind:name="'<?= $paymentForm->formName() ?>[payments]['+ index +'][invoice_amount]'"
                   v-model="pay.amount">
            <input type="hidden"
                   v-bind:name="'<?= $paymentForm->formName() ?>[payments]['+ index +'][user_wallet_id]'"
                   v-model="pay.wallet_id">

            <div class="payment__row valign_middle">
              <div class="col-payment__col payment__col_left payment__col_send">
                <span class="payments__amount" v-html="amountFormatter(pay.amount) + ' ' + icon(false)"></span>
                <span class="payments__type_icon hidden-xs">
                    <img v-bind:src="'../../../img/payments/' +  pay.icon " alt="">
                  </span>
                <span class="payments__type_name">
                    {{ pay.name }}
                    <i class="payments_bonus"
                       v-bind:class="paymentsBonus(pay.bonus)">
                      {{ octothorpe(round(pay.bonus)) }}%
                    </i>
                  </span>
                <span class="wallet__address-dynamic">{{ pay.address }}</span>
              </div>
              <div class="payment__col payment__col_right text-right">
                <div class="payments__sum">
                  <span><?= Yii::_t('partners.payments.payment_result') ?>:</span>
                  <span class="payments__sum_pay"
                        v-html="amountFormatter(getConvertedAmountWithBonusByPay(pay)) + ' ' + pay.iconConvert"></span>
                  <span class="payments_bonus"
                        v-bind:class="paymentsBonus(pay.bonus)"
                        v-html="'( ' + octothorpe(round(bonus(pay, pay.amount)), true) + ' ' + icon(false) + ' )'">
                              </span>
                  <span class="payments__delete" @click="removePay(pay)">
                                <i class="icon-delete"></i>
                              </span>
                </div>
              </div>
            </div>
          </li>
        </ul>
        <div class="content__position">
          <div class="row">
            <div class="col-xs-6">
              <button class="btn btn-default" v-bind:disabled="!paySum">
                <?= Yii::_t('partners.payments.do_request') ?></button>
            </div>
            <div v-if="paySum > 0" v-cloak class="col-xs-6 text-right">
              <div class="payments__sum payments__sum_footer">
                <span><?= Yii::_t('partners.payments.request_total') ?>:</span>
                <span class="payments__sum_pay" v-html="amountFormatter(paySum) + ' ' + icon(false)"></span>
              </div>
            </div>
          </div>

        </div>
        <?php AjaxActiveForm::end() ?>
      </div>
    </div>
  </div>

<?php
$this->registerJs(<<<JS
$('#pjax-balance').on('pjax:complete', function() {
  var pjaxBlock = $(this); 
  $('html, body').animate({
      scrollTop: pjaxBlock.find('.payments-table-col').offset().top
    }, 100);
});
JS
);
?>