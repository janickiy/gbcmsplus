<?php

use mcms\partners\assets\PaymentsAsset;
use mcms\partners\models\EarlyPaymentRequestForm;
use mcms\payments\models\PartnerPaymentSettings;
use mcms\payments\models\UserPayment;
use rgk\utils\components\CurrenciesValues;
use yii\bootstrap\Html as BHtml;
use yii\grid\GridView;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\i18n\Formatter;
use yii\widgets\Pjax;
use mcms\common\helpers\ArrayHelper;

/** @var \yii\data\ActiveDataProvider $invoices */
/** @var \mcms\payments\components\UserBalance $currentBalance */
/** @var \mcms\payments\components\UserBalance $oldBalance */
/** @var \yii\data\ActiveDataProvider $payments */
/** @var EarlyPaymentRequestForm $paymentForm */
/** @var \mcms\payments\Module $paymentsModule */
/** @var array[] $paymentSystems */
/** @var array[] $courses */
/** @var \yii\web\View $this */
/** @var boolean $hasOldBalance */
/** @var boolean $hasOldPayments */
/** @var boolean $paymentsIsDisabled */
/** @var boolean $showLocal */
/** @var boolean $showLocalFirst */
/** @var double $convertedSum */
/** @var \mcms\payments\components\UserBalance $fixBalance */
/** @var bool $hasWalletDetailsAccess */
/** @var CurrenciesValues $totalPayed */
/** @var CurrenciesValues $totalCharged */
/** @var PartnerPaymentSettings $partnerPaymentSettings */
/** @var \mcms\payments\models\UserWallet[] $userWallets */

PaymentsAsset::register($this);
$this->context->pageTitle = $this->context->controllerTitle . ' - ' . Yii::_t('payments.menu-payments');
$payments->setPagination(['pageSize' => 25]);
$paymentsModule = Yii::$app->getModule('payments');

$currencyIcons = [
  'rub' => 'Р',
  'usd' => '$',
  'eur' => '€',
];
?>

  <script>
    var ADD_SYSTEM_REQUEST = '<?= Url::to(['wallet-form']) ?>?currency=<?= $currentBalance->getCurrency() ?>&type=';
    var RUR = '<?= $paymentsModule::RUB ?>';
    var USD = '<?= $paymentsModule::USD ?>';
    var EUR = '<?= $paymentsModule::EUR ?>';
    window.data = {
      balance: '<?= ($mainBalance = $fixBalance->getMain()) < 0 ? 0 : $mainBalance ?>',
      currency: '<?= $fixBalance->getCurrency() ?>',
      courses: '<?= Json::encode($courses)?>',
      payment_systems: <?= Json::encode($paymentSystems) ?>,
      showLocal: <?= Json::encode($showLocal)?>,
      showLocalFirst: <?= Json::encode($showLocalFirst) ?>
    };
    window.isYiiDev = <?= Json::encode(YII_DEBUG && YII_ENV_DEV) ?>;
    window.IS_PASSWORD_CONFIRMED = <?= $hasWalletDetailsAccess ? 'true' : 'false' ?>;
  </script>

  <div class="container-fluid">
    <div class="row">

      <?= !$paymentsIsDisabled
        ? $this->render('_payment_order', compact(
          'fixBalance',
          'courses',
          'currentBalance',
          'oldBalance',
          'paymentForm',
          'paymentSystems',
          'hasOldBalance',
          'hasOldPayments',
          'convertedSum',
          'showLocal',
          'showLocalFirst',
          'hasWalletDetailsAccess',
          'partnerPaymentSettings',
          'userWallets'
        ))
        : null;
      ?>

      <?php Pjax::begin([
        'id' => 'pjax-balance',
      ]) ?>
      <div class="col-xs-12 payments-table-col">
        <div class="bgf">
          <div class="title"><h2><?= Yii::_t('payments.payments-payments-list') ?></h2></div>

          <?php if (!$payments->getTotalCount()): ?>
            <div class="empty_data empty_data-payments">
              <i class="icon-no_data"></i>
              <span><?= Yii::_t('payments.payments-empty-payments-list') ?></span>
            </div>
          <?php else: ?>
            <?= GridView::widget([
              'dataProvider' => $payments,
              'showFooter' => true,
              'formatter' => ['class' => \yii\i18n\Formatter::class, 'nullDisplay' => ''],
              'tableOptions' => [
                'class' => 'table table-striped table-payments'
              ],
              'summary' => '',
              'layout' => "{items}\n<div class='text-center'>{pager}</div>",
              'columns' => [
                [
                  'attribute' => 'id',
                  'contentOptions' => [
                    'data-label' => 'ID',
                  ]
                ],
                [
                  'attribute' => 'created_at',
                  'format' => 'datetime',
                  'contentOptions' => function (UserPayment $model) {
                    return [
                      'data-label' => $model->getAttributeLabel('created_at'),
                    ];
                  }
                ],
                [
                  'attribute' => 'payed_at',
                  'format' => 'datetime',
                  'contentOptions' => function (UserPayment $model) {
                    return [
                      'data-label' => $model->getAttributeLabel('payed_at'),
                    ];
                  },
                  'value' => function (UserPayment $model) {
                    return $model->status === $model::STATUS_COMPLETED ? $model->payed_at : null;
                  }
                ],
                [
                  'label' => Yii::_t('partners.payments.written_off'),
                  'value' => function (UserPayment $model) use ($currencyIcons) {
                    $invoiceAmount = round($model->invoice_amount, 2);
                    return Yii::$app->formatter->asDecimal($invoiceAmount) . ' ' . ArrayHelper::getValue($currencyIcons, $model->invoice_currency);
                  },
                  'contentOptions' => [
                    'data-label' => Yii::_t('partners.payments.written_off'),
                  ],
                  'footer' => $this->render('_total_payed', ['currencyIcons' => $currencyIcons, 'values' => $totalCharged])
                ],
                [
                  'label' => Yii::_t('partners.payments.payments-commission'),
                  'value' => function (UserPayment $model) use ($currencyIcons) {
                    $commission = $model->calcResellerCommission(true);
                    return ($commission->amount > 0 ? '+' : '')
                      . Yii::$app->formatter->asDecimal($commission->amount) . ' '
                      . ArrayHelper::getValue($currencyIcons, $commission->currency);
                  },
                  'contentOptions' => function (UserPayment $model) {
                    $commission = $model->calcResellerCommission(true);
                    return [
                      'class' => $commission->amount > 0 ? 'status__ok' : 'status__fail',
                      'data-label' => Yii::_t('partners.payments.payments-commission')
                    ];
                  }
                ],
                [
                  'attribute' => 'amount',
                  'label' => Yii::_t('partners.payments.to_payout'),
                  'contentOptions' => function (UserPayment $model) {
                    return [
                      'data-label' => $model->getAttributeLabel('amount'),
                    ];
                  },
                  'value' => function (UserPayment $model) use ($currencyIcons) {
                    return Yii::$app->formatter->asDecimal($model->amount) . ' ' . ArrayHelper::getValue($currencyIcons, $model->currency);
                  },
                  'footer' => $this->render('_total_payed', ['currencyIcons' => $currencyIcons, 'values' => $totalPayed])
                ],
                [
                  'attribute' => 'user_wallet_id',
                  'value' => function (UserPayment $model) {
                    return $model->userWallet->getWalletTypeLabel();
                  },
                  'contentOptions' => function (UserPayment $model) {
                    return [
                      'title' => $model->userWallet->getAccountObject()->getUniqueValueProtected(),
                      'data-label' => $model->getAttributeLabel('user_wallet_id')
                    ];
                  },
                ],
                [
                  'attribute' => 'status',
                  'format' => 'raw',
                  'value' => function (UserPayment $model) {
                    $class = '';
                    $tooltip = '';
                    switch ($model->status) {
                      case $model::STATUS_COMPLETED:
                        $class = 'status__ok';
                        break;
                      case $model::STATUS_DELAYED:
                        $class = 'status__warning';
                        if ($model->pay_period_end_date) {
                          $tooltip = BHtml::tag('i', '', [
                            'data-toggle' => 'tooltip',
                            'data-placement' => 'top',
                            'class' => 'icon-question',
                            'data-original-title' => Yii::_t('partners.payments.delayed_till', [
                              // todo возможно во всём гриде надо использовать обычный Yii::$app->formatter.
                              ':date' => (new Formatter)->asDate($model->pay_period_end_date)
                            ])
                          ]);
                        }
                        break;
                      case $model::STATUS_ANNULLED:
                      case $model::STATUS_CANCELED:
                        $class = 'status__fail';
                        $tooltip = BHtml::tag('i', '', [
                          'data-toggle' => 'tooltip',
                          'data-placement' => 'top',
                          'class' => 'icon-question',
                          'data-original-title' => $model->description
                        ]);
                        break;
                    }
                    if ($model->status == $model::STATUS_COMPLETED && $model->cheque_file) {
                      $tooltip .= BHtml::a(BHtml::tag('i', '', [
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top',
                        'class' => 'icon-file',
                        'data-original-title' => $model::translate('download-check'),
                      ]), $model->getUploadedFileUrl('cheque_file'), [
                        'data-pjax' => 0,
                        'target' => '_blank',
                        'class' => 'invoice-icon'
                      ]);
                    }

                    if ($model->generated_invoice_file_positive) {
                      $tooltip .= BHtml::a(BHtml::tag('i', '', [
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top',
                        'class' => 'icon-file',
                        'data-original-title' => $model::translate('generated_invoice_file_positive'),
                      ]), $model->getUploadedFileUrl('generated_invoice_file_positive'), [
                        'data-pjax' => 0,
                        'target' => '_blank',
                        'class' => 'invoice-icon',
                      ]);
                    }

                    if ($model->generated_invoice_file_negative) {
                      $tooltip .= BHtml::a(BHtml::tag('i', '', [
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top',
                        'class' => 'icon-file danger',
                        'style' =>'background-color: #FF413650',
                        'data-original-title' => $model::translate('generated_invoice_file_negative'),
                      ]), $model->getUploadedFileUrl('generated_invoice_file_negative'), [
                        'data-pjax' => 0,
                        'target' => '_blank',
                        'class' => 'invoice-icon',
                      ]);
                    }

                    return BHtml::tag('div', $model->getStatusLabelPartner() . $tooltip, [
                      'class' => $class
                    ]);
                  },
                  'contentOptions' => function (UserPayment $model) {
                    return [
                      'data-label' => $model->getAttributeLabel('status'),
                    ];
                  },
                ]
              ]
            ]); ?>
          <?php endif ?>
        </div>
      </div>
      <?php /** нужен реинит после пджакс */
      $this->registerJs('$(\'[data-toggle="tooltip"]\').tooltip();'); ?>
      <?php Pjax::end() ?>
    </div>
  </div>

  <template id="paymentItemTemplate">
    <li
        @click="$parent.addPay(system)"
    >
      <div
          class="payment"
          v-bind:class="paymentClasses()"
          @click="paymentClick"
      >
        <template v-if="Object.keys(system.groupWallets).length > 1">
          <i
              class="payment__icon"
              v-bind:class="{'icon-next': !openWallets, 'icon-down2': openWallets}"
          ></i>
        </template>
        <template v-if="!system.active">
          <i class="payment__icon icon-plus1"></i>
        </template>
        <div class="payment__row">
          <div class="payment__col payment__col_left">
          <span class="payments__type_icon hidden-xs">
              <img v-bind:src="'../../../img/payments/' +  system.icon "
                   alt="">
          </span>
            <span class="payments__type_name">
            {{ system.name }}
            <i class="payments_bonus"
               v-bind:class="$parent.paymentsBonus(system.bonus)">
                {{ $parent.octothorpe(system.bonus) }}%
            </i>
            <div class="payments__wallet_address" v-if="Object.keys(system.groupWallets).length === 1">{{ system.wallets[0].address }}</div>
            <div class="payments__min_amount"
                 v-if="system.active">
                <template v-if="system.min">
                  <span class="minmax-wrapper">
                    <?= Yii::_t('partners.payments.limit_min_short') ?>:
                    <span
                        class="minmax-limit"
                        v-html="$parent.amountFormatter($parent.ceil(system.min)) + ' ' + $parent.icon(false)"
                        :class="{ 'error' : amount > 0 && amount < system.min }"
                        @click.stop="$parent.setAmount($parent.ceil(system.min))"
                    >
                    </span>
                  </span>
                </template>
                <template v-if="system.max">
                  <span class="minmax-wrapper">
                    <?= Yii::_t('partners.payments.limit_max_short') ?>:
                    <span
                        class="minmax-limit"
                        v-html="$parent.amountFormatter($parent.floor(system.max)) + ' ' + $parent.icon(false)"
                        :class="{ 'error' : amount > system.max }"
                        @click.stop="$parent.setAmount($parent.floor(system.max))"
                    >
                    </span>
                  </span>
                </template>
            </div>
        </span>
          </div>
          <div class="payment__col payment__col_right text-right payments__info">
            <div class="payments__sum">
              <template v-if="Object.keys(system.groupWallets).length === 1">
                <template v-if="system.wallets.length === 1">
                <span
                    class="payments__sum_pay payments__sum_pay_blue payments__sum_pay_cursor"
                    v-for="innerWallet in system.groupWallets[Object.keys(system.groupWallets)[0]]"
                    :class="!$parent.groupValid(system, innerWallet) ? 'has-error' : ''"
                    v-html="$parent.amountFormatter($parent.getConvertedAmountWithBonusByWallet(system, innerWallet)) + ' ' + $parent.icon(innerWallet.currency)"
                ></span>
                </template>
                <template v-else>
                <span
                    class="payments__sum_pay payments__sum_pay_blue payments__sum_pay_cursor"
                    v-for="innerWallet in system.groupWallets[Object.keys(system.groupWallets)[0]]"
                    @click="$parent.addWalletPay(system, innerWallet)"
                    :class="!$parent.groupValid(system, innerWallet) ? 'has-error' : ''"
                    v-html="$parent.amountFormatter($parent.getConvertedAmountWithBonusByWallet(system, innerWallet)) + ' ' + $parent.icon(innerWallet.currency)"
                ></span>
                </template>
              </template>
              <template v-else>
              <span
                  class="payments__sum_pay"
                  v-for="currency in system.currency"
                  v-bind:class="[
                $parent.paysystemError(system, currency)
                    ? 'has-error'
                    : [system.activeCurrency.indexOf(currency) !== -1 ? 'payments__sum_pay_blue' : 'payments__sum_pay_gray']
                ]"
                  v-html="$parent.amountFormatter($parent.getConvertedAmountWithBonusByValue(system, amount, currency)) + ' ' + $parent.icon(currency)"
              >
              </span>
              </template>
              <template v-if="systemBonusToInt(system.bonus) !== 0">
              <span class="payments_bonus"
                    v-bind:class="$parent.paymentsBonus(system.bonus)"
                    v-html="'( ' + ($parent.octothorpe($parent.round($parent.bonus(system, $parent.convertAmount(amount, false))), true) || 0) + ' ' + $parent.icon(false) + ' )'"
              ></span>
              </template>
            </div>
            <template v-if="Object.keys(system.groupWallets).length === 1">
              <template v-if="system.groupWallets[Object.keys(system.groupWallets)[0]][0].isRelatedLimits">
                <div
                    class="payments__min_amount"
                    v-bind:class="$parent.walletError(system.groupWallets[Object.keys(system.groupWallets)[0]][0], system)"
                >
              <span
                  v-if="system.groupWallets[Object.keys(system.groupWallets)[0]][0].dayLimit || system.groupWallets[Object.keys(system.groupWallets)[0]][0].monthLimit"
              >
                <?= Yii::_t('partners.payments.limits') ?>:
              </span>
                  <span
                      class="daymonth-limit"
                      v-if="system.groupWallets[Object.keys(system.groupWallets)[0]][0].dayLimit"
                      :class="{ 'error' : !$parent.validateRelatedLimits(system.groupWallets[Object.keys(system.groupWallets)[0]], system) }"
                      v-html="$parent.amountFormatter($parent.availableDayLimit(system.groupWallets[Object.keys(system.groupWallets)[0]][0])) + ' ' + $parent.iconsArray(system.groupWallets[Object.keys(system.groupWallets)[0]]) + '/<?= Yii::_t('partners.payments.daily_limit_short') ?>' + (system.groupWallets[Object.keys(system.groupWallets)[0]][0].dayLimit && system.groupWallets[Object.keys(system.groupWallets)[0]][0].monthLimit ? ',' : '')"
                  >
              </span>
                  <span
                      class="daymonth-limit"
                      v-if="system.groupWallets[Object.keys(system.groupWallets)[0]][0].monthLimit"
                      :class="{ 'error' : !$parent.validateMonthLimit(system.groupWallets[Object.keys(system.groupWallets)[0]][0], system) }"
                      v-html="$parent.amountFormatter($parent.round(parseFloat(system.groupWallets[Object.keys(system.groupWallets)[0]][0].monthLimit) - parseFloat(system.groupWallets[Object.keys(system.groupWallets)[0]][0].usedMonth))) + ' ' + $parent.iconsArray(system.groupWallets[Object.keys(system.groupWallets)[0]]) + '/<?= Yii::_t('partners.payments.monthly_limit_short') ?>'"
                  >
              </span>
                </div>
              </template>
              <template v-else>
                <div
                    class="payments__min_amount"
                    v-for="innerWallet in system.groupWallets[Object.keys(system.groupWallets)[0]]"
                    v-bind:class="$parent.walletError(innerWallet, system)"
                >
              <span
                  v-if="innerWallet.dayLimit || innerWallet.monthLimit"><?= Yii::_t('partners.payments.limits') ?>
                :</span>
                  <span
                      class="daymonth-limit"
                      v-if="innerWallet.dayLimit"
                      :class="{ 'error' : !$parent.validateDayLimit(innerWallet, system) }"
                      v-html="$parent.amountFormatter($parent.availableDayLimit(innerWallet)) + ' ' + $parent.icon(innerWallet.currency) + '/<?= Yii::_t('partners.payments.daily_limit_short') ?>' + (innerWallet.dayLimit && innerWallet.monthLimit ? ',' : '')"
                  >
                </span>
                  <span
                      class="daymonth-limit"
                      v-if="innerWallet.monthLimit"
                      :class="{ 'error' : !$parent.validateMonthLimit(innerWallet, system) }"
                      v-html="$parent.amountFormatter($parent.round(parseFloat(innerWallet.monthLimit) - parseFloat(innerWallet.usedMonth))) + ' ' + $parent.icon(innerWallet.currency) + '/<?= Yii::_t('partners.payments.monthly_limit_short') ?>'"
                  >
                </span>
                </div>
              </template>
            </template>
          </div>
        </div>
      </div>
      <div class="wallets" v-if="Object.keys(system.groupWallets).length > 1">
        <div
            class="wallet"
            v-for="wallet in system.groupWallets"
        >
          <i class="icon-next"></i>
          <div class="payment__row">
            <div class="col-payment__col payment__col_left">
              <div class="wallet__address">{{ wallet[0].address ? wallet[0].address : system.name }}
              </div>
            </div>
            <div class="payment__col payment__col_right text-right">
            <span
                class="wallet__payment"
                v-for="innerWallet in wallet"
                @click="$parent.addWalletPay(system, innerWallet)"
                :class="$parent.walletError(innerWallet, system)"
                v-html="$parent.amountFormatter($parent.getConvertedAmountWithBonusByWallet(system, innerWallet)) + ' ' + $parent.icon(innerWallet.currency)"
            ></span>
              <template v-if="wallet[0].isRelatedLimits">
                <div
                    class="payments__min_amount"
                    v-if="wallet[0].dayLimit && wallet[0].monthLimit"
                >
                  <?= Yii::_t('partners.payments.limits') ?>:
                  <span
                      class="daymonth-limit"
                      v-if="wallet[0].dayLimit"
                      :class="{ 'error' : !$parent.validateRelatedLimits(wallet, system) }"
                      v-html="$parent.amountFormatter($parent.availableDayLimit(wallet[0])) + ' ' + $parent.iconsArray(wallet) + '/<?= Yii::_t('partners.payments.daily_limit_short') ?>' + (system.dayLimit && system.monthLimit ? ',' : '')"
                  >
                </span>
                  <span
                      class="daymonth-limit"
                      v-if="wallet[0].monthLimit"
                      :class="{ 'error' : !$parent.validateMonthLimit(wallet[0], system) }"
                      v-html="$parent.amountFormatter(wallet[0].monthLimit - wallet[0].usedMonth) + ' ' + $parent.iconsArray(wallet) + '/<?= Yii::_t('partners.payments.monthly_limit_short') ?>'"
                  >
                </span>
                </div>
              </template>
              <template v-else>
                <div
                    class="payments__min_amount"
                    v-for="innerWallet in wallet"
                    v-if="innerWallet.dayLimit && innerWallet.monthLimit"
                >
                  <?= Yii::_t('partners.payments.limits') ?>:
                  <span
                      class="daymonth-limit"
                      v-if="innerWallet.dayLimit"
                      :class="{ 'error' : !$parent.validateDayLimit(innerWallet, system) }"
                      v-html="$parent.amountFormatter($parent.availableDayLimit(innerWallet)) + ' ' + $parent.icon(innerWallet.currency) + '/<?= Yii::_t('partners.payments.daily_limit_short') ?>' + (system.dayLimit && system.monthLimit ? ',' : '')"
                  >
                </span>
                  <span
                      class="daymonth-limit"
                      v-if="innerWallet.monthLimit"
                      :class="{ 'error' : !$parent.validateMonthLimit(innerWallet, system) }"
                      v-html="$parent.amountFormatter(innerWallet.monthLimit - innerWallet.usedMonth) + ' ' + $parent.icon(innerWallet.currency) + '/<?= Yii::_t('partners.payments.monthly_limit_short') ?>'"
                  >
                </span>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </li>
  </template>

<?= $this->render('_password_modal') ?>