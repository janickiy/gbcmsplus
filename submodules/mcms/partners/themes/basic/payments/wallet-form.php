<?php
use mcms\common\form\AjaxActiveForm;
use mcms\partners\components\widgets\PriceWidget;
use mcms\payments\models\UserWallet;
use mcms\payments\models\wallet\AbstractWallet;
use mcms\payments\models\wallet\Card;
use mcms\payments\models\wallet\Epayments;
use mcms\payments\models\wallet\JuridicalPerson;
use mcms\payments\models\wallet\Paxum;
use mcms\payments\models\wallet\PayPal;
use mcms\payments\models\wallet\PrivatePerson;
use mcms\payments\models\wallet\Qiwi;
use mcms\payments\models\wallet\Wallet;
use mcms\payments\models\wallet\WebMoney;
use mcms\payments\models\wallet\wire\iban\Wire;
use mcms\payments\models\wallet\Yandex;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
/** @var AbstractWallet $model ID платежной системы */
/** @var string $ps ID платежной системы */
/** @var UserWallet $userWallet Кошелек */
// TRICKY ПОЛЯ И СКРИПТЫ ДОЛЖНЫ БЫТЬ УНИКАЛЬНЫ. НУЖНО ПРИПИСЫВАТЬ $ps К КАЖДОМУ ИДЕНТИФИКАТОРУ

// Алиас для ID платежной системы
$ps = $model->getType();
$paysystemCode = $userWallet->walletType->code;

/** @var AbstractWallet $model */
/** @var UserWallet $userWallet */
/** @var bool $isLocked */
/** @var array $wallets */
/** @var int $walletType */
/** @var  $availableCurrencies array */
/** @var  $walletsHandler \mcms\partners\components\WalletsDisplayHandler */

$walletFormAction = Url::to([
  'payments/wallet-form',
  'walletId' => $userWallet->id,
  'type' => $model->getType(),
  'currency' => $model->getSelectedCurrency(),
  'new' => $userWallet->isNewRecord
]);
$walletListUrl = Url::to(['payments/wallets-list', 'type' => $model->getType()]);
$submitButtonId = 'submit-partner-wallet-' . $ps;

// меняем экшен формы, чтобы можно было редактировать кошелек без перезагрузки страницы
// (убираем &new=1)
$this->registerJs("
(function() {
var walletForm = $('#walletSettings-$ps');

walletForm.attr('action', '$walletFormAction');

walletForm.on('beforeValidateAttribute', function() {
  window.ajaxValidate = true;
});    
walletForm.on('afterValidateAttribute', function() {
  window.ajaxValidate = false;
});  
})();
");
?>

<?php $form = AjaxActiveForm::begin([
  'id' => 'walletSettings-' . $ps,
  'options' => [
    'class' => 'js-wallet-form',
    'data' => [
      'paysystem-id' => $userWallet->wallet_type,
      'wallet-id' => $userWallet->id,
    ],
  ],
  'action' => $walletFormAction,
  // TODO userWallets вроде как удалили так-то в JS
  'ajaxSuccess' => new JsExpression(
  /** @lang JavaScript */
    '
  function(data) {
    if (data.success) {
      var walletInfo = data.data;
      window.userWallets && window.userWallets.add(walletInfo.walletType, walletInfo.walletCurrency, walletInfo.walletUniqueValue);
      $(".profile-finance input[name=merchant]").filter("[value=' . $model->getType() . ']").closest("label").addClass("filled");
      $(document).trigger("wallet_save_success", [walletInfo]);
    }
  }
'),
  'forceResultMessages' => true,
]); ?>

<div class="title">
    <h2><?= Yii::_t('partners.wallets-manage.title_' . ($userWallet->isNewRecord ? 'create' : 'update') . '_' . $paysystemCode) ?></h2>
</div>
<?php $formatter = clone Yii::$app->formatter; ?>
<?php $formatter->nullDisplay = '-'; ?>

  <div class="content__position content__position_padding">
    <a href="javascript:void(0)" class="control-label" data-toggle="collapse" data-target="#wallet-info-block-<?= $ps ?>">
      <?= Yii::_t('partners.payments.pay_system_info') ?>
    </a>
  </div>
    <div id="wallet-info-block-<?= $ps ?>" class="wallet-info collapse">
      <div class="wallet-info__row">
        <div class="wallet-info__half">
          <div class="wallet-info__cell wallet-info__caption">
            <?= Yii::_t('partners.payments.requisites_commission') ?>:
          </div>
            <?php $calcResellerPercent = $model->calcResellerPercent(Yii::$app->user->id) ?>
          <div class="wallet-info__cell wallet-info__cell_<?= ($calcResellerPercent > 0
            ?  'positive' : ($calcResellerPercent == 0 ? '' : 'negative'))
          ?>"><?= $calcResellerPercent > 0 ? '+' : '' ?><?= $calcResellerPercent + 0 ?>%
            <?= ($walletComission = $model->getWalletCommissionInfo())
              ? Html::tag('b', '(' . $walletComission . ')')
              : '' ?>
          </div>
        </div>
      </div>
      <?php if (in_array('rub', $model->getActiveCurrencies()) && ($model->getMinPayoutSumRub() || $model->getMaxPayoutByCurrency('rub'))): ?>
      <div class="wallet-info__row">
        <?php if ($model->getMinPayoutSumRub()): ?>
          <div class="wallet-info__half">
            <div class="wallet-info__cell wallet-info__caption">
              <?= Yii::_t('partners.payments.min-payout') ?> (<?=Yii::_t('partners.payments.rub_name')?>):
            </div>
            <div class="wallet-info__cell"><?= PriceWidget::widget([
                'value' => $model->getMinPayoutSumRub(),
                'currency' => 'rub',
              ]) ?>
            </div>
          </div>
        <?php endif; ?>
        <?php if ($model->getMaxPayoutByCurrency('rub')): ?>
          <div class="wallet-info__half">
            <div class="wallet-info__cell wallet-info__caption"><?= Yii::_t('partners.payments.max-payout') ?>:
            </div>
            <div class="wallet-info__cell"><?= PriceWidget::widget([
                'value' => $model->getMaxPayoutByCurrency('rub'),
                'currency' => 'rub',
              ]) ?></div>
          </div>
        <?php endif; ?>
      </div>
      <?php endif;?>
      <?php if (in_array('usd', $model->getActiveCurrencies()) && ($model->getMinPayoutSumUsd() || $model->getMaxPayoutByCurrency('usd'))): ?>
      <div class="wallet-info__row">
        <?php if ($model->getMinPayoutSumUsd()): ?>
          <div class="wallet-info__half">
            <div class="wallet-info__cell wallet-info__caption">
              <?= Yii::_t('partners.payments.min-payout') ?> (<?=Yii::_t('partners.payments.usd_name')?>):
            </div>
            <div class="wallet-info__cell"><?= PriceWidget::widget([
                'value' => $model->getMinPayoutSumUsd(),
                'currency' => 'usd',
              ]) ?></div>
          </div>
        <?php endif; ?>
        <?php if ($model->getMaxPayoutByCurrency('usd')): ?>
          <div class="wallet-info__half">
            <div class="wallet-info__cell wallet-info__caption"><?= Yii::_t('partners.payments.max-payout') ?>:
            </div>
            <div class="wallet-info__cell"><?= PriceWidget::widget([
                'value' => $model->getMaxPayoutByCurrency('usd'),
                'currency' => 'usd',
              ]) ?></div>
          </div>
        <?php endif; ?>
      </div>
      <?php endif;?>
      <?php if (in_array('eur', $model->getActiveCurrencies()) && ($model->getMinPayoutSumEur() || $model->getMaxPayoutByCurrency('eur'))): ?>
      <div class="wallet-info__row">
        <?php if ($model->getMinPayoutSumEur()): ?>
          <div class="wallet-info__half">
            <div class="wallet-info__cell wallet-info__caption">
              <?= Yii::_t('partners.payments.min-payout') ?> (<?=Yii::_t('partners.payments.eur_name')?>):
            </div>
            <div class="wallet-info__cell"><?= PriceWidget::widget([
                'value' => $model->getMinPayoutSumEur(),
                'currency' => 'eur',
              ]) ?></div>
          </div>
        <?php endif; ?>
        <?php if ($model->getMaxPayoutByCurrency('eur')): ?>
          <div class="wallet-info__half">
            <div class="wallet-info__cell wallet-info__caption"><?= Yii::_t('partners.payments.max-payout') ?>:
            </div>
            <div class="wallet-info__cell"><?= PriceWidget::widget([
                'value' => $model->getMaxPayoutByCurrency('eur'),
                'currency' => 'eur',
              ]) ?></div>
          </div>
        <?php endif; ?>
      </div>
      <?php endif;?>
      <?php if (in_array('rub', $model->getActiveCurrencies()) && ($model->getPayoutLimitDailyByCurrency('rub') || $model->getPayoutLimitMonthlyByCurrency('rub'))): ?>
      <div class="wallet-info__row">
        <?php if ($model->getPayoutLimitDailyByCurrency('rub') && ($model->getPayoutLimitDailyByCurrency('rub') || $model->getPayoutLimitMonthlyByCurrency('rub'))): ?>
          <div class="wallet-info__half">
            <div class="wallet-info__cell wallet-info__caption">
              <?= Yii::_t('partners.payments.daily-limit') ?> (<?=Yii::_t('partners.payments.rub_name')?>):
            </div>
            <div class="wallet-info__cell"><?= PriceWidget::widget([
                'value' => $model->getPayoutLimitDailyByCurrency('rub'),
                'currency' => 'rub',
              ]) ?></div>
          </div>
        <?php endif; ?>
        <?php if ($model->getPayoutLimitMonthlyByCurrency('rub')): ?>
          <div class="wallet-info__half">
            <div class="wallet-info__cell wallet-info__caption"><?= Yii::_t('partners.payments.monthly-limit') ?>:
            </div>
            <div class="wallet-info__cell"><?= PriceWidget::widget([
                'value' => $model->getPayoutLimitMonthlyByCurrency('rub'),
                'currency' => 'rub',
              ]) ?></div>
          </div>
        <?php endif; ?>
      </div>
      <?php endif;?>
      <?php if (in_array('usd', $model->getActiveCurrencies()) && ($model->getPayoutLimitDailyByCurrency('usd') || $model->getPayoutLimitMonthlyByCurrency('usd'))): ?>
      <div class="wallet-info__row">
        <?php if ($model->getPayoutLimitDailyByCurrency('usd')): ?>
          <div class="wallet-info__half">
            <div class="wallet-info__cell wallet-info__caption">
              <?= Yii::_t('partners.payments.daily-limit') ?> (<?=Yii::_t('partners.payments.usd_name')?>):
            </div>
            <div class="wallet-info__cell"><?= PriceWidget::widget([
                'value' => $model->getPayoutLimitDailyByCurrency('usd'),
                'currency' => 'usd',
              ]) ?></div>
          </div>
        <?php endif; ?>
        <?php if ($model->getPayoutLimitMonthlyByCurrency('usd')): ?>
          <div class="wallet-info__half">
            <div class="wallet-info__cell wallet-info__caption"><?= Yii::_t('partners.payments.monthly-limit') ?>:
            </div>
            <div class="wallet-info__cell"><?= PriceWidget::widget([
                'value' => $model->getPayoutLimitMonthlyByCurrency('usd'),
                'currency' => 'usd',
              ]) ?></div>
          </div>
        <?php endif; ?>
      </div>
      <?php endif;?>
      <?php if (in_array('eur', $model->getActiveCurrencies()) && ($model->getPayoutLimitDailyByCurrency('eur') || $model->getPayoutLimitMonthlyByCurrency('eur'))): ?>
      <div class="wallet-info__row">
        <?php if ($model->getPayoutLimitDailyByCurrency('eur')): ?>
          <div class="wallet-info__half">
            <div class="wallet-info__cell wallet-info__caption">
              <?= Yii::_t('partners.payments.daily-limit') ?> (<?=Yii::_t('partners.payments.eur_name')?>):
            </div>
            <div class="wallet-info__cell"><?= PriceWidget::widget([
                'value' => $model->getPayoutLimitDailyByCurrency('eur'),
                'currency' => 'eur',
              ]) ?></div>
          </div>
        <?php endif; ?>
        <?php if ($model->getPayoutLimitMonthlyByCurrency('eur')): ?>
          <div class="wallet-info__half">
            <div class="wallet-info__cell wallet-info__caption"><?= Yii::_t('partners.payments.monthly-limit') ?>:
            </div>
            <div class="wallet-info__cell"><?= PriceWidget::widget([
                'value' => $model->getPayoutLimitMonthlyByCurrency('eur'),
                'currency' => 'eur',
              ]) ?></div>
          </div>
        <?php endif; ?>
      </div>
      <?php endif;?>
      <div class="wallet-info__row">
        <div class="wallet-info__cell wallet-info__cell_icon">
          <i class="icon-danger"></i>
          <?= Yii::_t('partners.payments.available_limits_help_message') ?>.
        </div>
      </div>
      <?php if ($model->getInfo()): ?>
      <div class="wallet-info__row">
        <div class="wallet-info__cell">
          <?= $model->getInfo() ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

  <div class="content__position">
    <?php foreach ($model->getForm($form, $userWallet->id)->createFormFields([
      'options' => [
        'class' => 'form-group' . ($isLocked ? ' input_readonly' : '')
      ],
      'inputOptions' => [
        'class' => 'form-control',
        'readonly' => $isLocked
      ],
      'template' => $isLocked
        ?
        '{label}{input}<span data-toggle="tooltip" data-placement="top" title="" data-original-title="' . Yii::_t('partners.payments.requisites-danger-input') . '"><i class="icon-lock"></i></span>{hint}{error}'
        :
        '{label}{input}{hint}{error}'
    ], '#' . $submitButtonId) as $field): ?>
      <?= $field; ?>
    <?php endforeach ?>

      <?php
      // TODO В идеале валюты по умолчанию должны ставится не хайден полями, а на сервере, например сделать: AbstractWallet::$defaultCurrency, AbstractWallet::$manualChangeCurrency = false

      // TODO Тудушка ниже вероятнее всего уже не актуальна
      // TODO КОСТЫЛЬ для автоматического определения валюты веб Мани. Надо:
        /*
         * - В AbstractWallet добавить параметр isAutoCurrency или что-нибудь подобное
         * - Если для параметр true, в UserWallet перед валидацией принудительно перезаписывать валюту кошелька, вызывая метод
         * $AbstractWallet->determineCurrency() (метод соответственно надо создать в ПС)
         * - метод determineCurrency() нужно так же определить в AbstractWallet и по умолчанию в методе выкидывать исключение, что метод не поддерживается ПС
         */
      ?>
      <?php if ($model instanceof Yandex) { ?>
        <div class="content__position_padding">
          <?= Yii::_t('partners.payments.currency_rub') ?>
          <?= Html::checkbox('currency[]', $walletsHandler->showCurrency('rub', $userWallet->isNewRecord, $availableCurrencies), ['id' => 'currencies-hidden-checkbox-rub-' . $ps, 'value' => 'rub', 'style' => 'display:none']) ?>
        </div>
      <?php } elseif ($model instanceof Qiwi) { ?>
        <div class="content__position_padding">
          <?= Yii::_t('partners.payments.currency_rub') ?>
          <?= Html::checkbox('currency[]', $walletsHandler->showCurrency('rub', $userWallet->isNewRecord, $availableCurrencies), ['id' => 'currencies-hidden-checkbox-rub-' . $ps, 'value' => 'rub', 'style' => 'display:none']) ?>
        </div>
      <?php } elseif ($model instanceof Paypal) { ?>
        <div class="content__position_padding">
          <?php if ($model->getWallet()->is_usd) { ?>
          <div class="checkbox checkbox-primary checkbox-inline">
            <?= Html::checkbox('currency[]', $walletsHandler->showCurrency('usd', $userWallet->isNewRecord, $availableCurrencies), ['id' => 'currencies-hidden-checkbox-usd-' . $ps, 'value' => 'usd']) ?>
            <label class="control-label" for="currencies-hidden-checkbox-usd-<?= $ps ?>">
              <?= Yii::_t('partners.payments.usd_name') ?>
            </label>
          </div>
          <?php } ?>
          <?php if ($model->getWallet()->is_eur) { ?>
          <div class="checkbox checkbox-primary checkbox-inline">
            <?= Html::checkbox('currency[]', $walletsHandler->showCurrency('eur', $userWallet->isNewRecord, $availableCurrencies), ['id' => 'currencies-hidden-checkbox-eur-' . $ps, 'value' => 'eur']) ?>
            <label class="control-label" for="currencies-hidden-checkbox-eur-<?= $ps ?>">
              <?= Yii::_t('partners.payments.eur_name') ?>
            </label>
          </div>
          <?php } ?>
        </div>
      <?php } elseif ($model instanceof Paxum) { ?>
        <div class="content__position_padding">
          <?php if ($model->getWallet()->is_usd) { ?>
          <div class="checkbox checkbox-primary checkbox-inline">
            <?= Html::checkbox('currency[]', $walletsHandler->showCurrency('usd', $userWallet->isNewRecord, $availableCurrencies), ['id' => 'currencies-hidden-checkbox-usd-' . $ps, 'value' => 'usd']) ?>
            <label class="control-label" for="currencies-hidden-checkbox-usd-<?= $ps ?>">
              <?= Yii::_t('partners.payments.usd_name') ?>
            </label>
          </div>
          <?php } ?>
          <?php if ($model->getWallet()->is_eur) { ?>
          <div class="checkbox checkbox-primary checkbox-inline">
            <?= Html::checkbox('currency[]', $walletsHandler->showCurrency('eur', $userWallet->isNewRecord, $availableCurrencies), ['id' => 'currencies-hidden-checkbox-eur-' . $ps, 'value' => 'eur']) ?>
            <label class="control-label" for="currencies-hidden-checkbox-eur-<?= $ps ?>">
              <?= Yii::_t('partners.payments.eur_name') ?>
            </label>
          </div>
          <?php } ?>
        </div>
      <?php }elseif ($model instanceof \mcms\payments\models\wallet\Capitalist) { ?>
          <script>
            <?php ob_start() ?>
            // Автоматическое определение валют webmoney
            function updateWebmoneyCurrency() {
                var wallet = $('#capitalist-wallet').val();
                var walletLetter = wallet.charAt(0);
              <?php
              $currenciesMap = [];
              if ($model->getWallet()->is_rub) {
                $currenciesMap[] = 'R: \'rub\'';
              }
              if ($model->getWallet()->is_usd) {
                $currenciesMap[] = 'U: \'usd\'';
              }
              if ($model->getWallet()->is_eur) {
                $currenciesMap[] = 'E: \'eur\'';
              }
              ?>
                var currenciesMap = {<?=implode(', ', $currenciesMap)?>};

                var currency = (typeof currenciesMap[walletLetter] !== 'undefined' ? currenciesMap[walletLetter] : '');

                $('#wallet-currency-<?= $ps ?>').val(currency);
            }

            $('body').on('change', '#capitalist-wallet', updateWebmoneyCurrency);
            updateWebmoneyCurrency();
            <?php $this->registerJs(ob_get_clean()) ?>
          </script>
      <?= Html::hiddenInput('currency', null, ['id' => 'wallet-currency-' . $ps]) ?>
      <?php }elseif ($model instanceof \mcms\payments\models\wallet\Usdt) { ?>
          <div class="content__position_padding">
            <?php if ($model->getWallet()->is_usd) { ?>
                <div class="checkbox checkbox-primary checkbox-inline">
                  <?= Html::checkbox('currency[]', $walletsHandler->showCurrency('usd', $userWallet->isNewRecord, $availableCurrencies), ['id' => 'currencies-hidden-checkbox-usd-' . $ps, 'value' => 'usd']) ?>
                    <label class="control-label" for="currencies-hidden-checkbox-usd-<?= $ps ?>">
                      <?= Yii::_t('partners.payments.usd_name') ?>
                    </label>
                </div>
            <?php } ?>
            <?php if ($model->getWallet()->is_eur) { ?>
                <div class="checkbox checkbox-primary checkbox-inline">
                  <?= Html::checkbox('currency[]', $walletsHandler->showCurrency('eur', $userWallet->isNewRecord, $availableCurrencies), ['id' => 'currencies-hidden-checkbox-eur-' . $ps, 'value' => 'eur']) ?>
                    <label class="control-label" for="currencies-hidden-checkbox-eur-<?= $ps ?>">
                      <?= Yii::_t('partners.payments.eur_name') ?>
                    </label>
                </div>
            <?php } ?>
          </div>
      <?php } elseif ($model instanceof Epayments) { ?>
        <div class="content__position_padding">
          <?php if ($model->getWallet()->is_usd) { ?>
          <div class="checkbox checkbox-primary checkbox-inline">
            <?= Html::checkbox('currency[]', $walletsHandler->showCurrency('usd', $userWallet->isNewRecord, $availableCurrencies), ['id' => 'currencies-hidden-checkbox-usd-' . $ps, 'value' => 'usd']) ?>
            <label class="control-label" for="currencies-hidden-checkbox-usd-<?= $ps ?>">
              <?= Yii::_t('partners.payments.usd_name') ?>
            </label>
          </div>
          <?php } ?>
          <?php if ($model->getWallet()->is_eur) { ?>
          <div class="checkbox checkbox-primary checkbox-inline">
            <?= Html::checkbox('currency[]', $walletsHandler->showCurrency('eur', $userWallet->isNewRecord, $availableCurrencies), ['id' => 'currencies-hidden-checkbox-eur-' . $ps, 'value' => 'eur']) ?>
            <label class="control-label" for="currencies-hidden-checkbox-eur-<?= $ps ?>">
              <?= Yii::_t('partners.payments.eur_name') ?>
            </label>
          </div>
          <?php } ?>
        </div>
      <?php } elseif ($model instanceof PrivatePerson) { ?>
        <div class="content__position_padding">
          <?= Yii::_t('partners.payments.currency_rub') ?>
          <?= Html::checkbox('currency[]', $walletsHandler->showCurrency('rub', $userWallet->isNewRecord, $availableCurrencies), ['id' => 'currencies-hidden-checkbox-rub-' . $ps, 'value' => 'rub', 'style' => 'display:none']) ?>
        </div>
      <?php } elseif ($model instanceof JuridicalPerson) { ?>
        <div class="content__position_padding">
          <?= Yii::_t('partners.payments.currency_rub') ?>
          <?= Html::checkbox('currency[]', $walletsHandler->showCurrency('rub', $userWallet->isNewRecord, $availableCurrencies), ['id' => 'currencies-hidden-checkbox-rub-' . $ps, 'value' => 'rub', 'style' => 'display:none']) ?>
        </div>
      <?php } elseif ($model instanceof Card) { ?>
        <script>
          <?php ob_start() ?>
          (function () {
            // Автоматическое определение валют карты
            var isApiAvailable = true;
            $('#walletSettings-<?= $ps ?>:not(.card_bind)').addClass('card_bind')
              .on('beforeValidateAttribute', function (event, attribute) {
                if (attribute.id !== 'card-card_number') return true;
                $('#card-card_number').addClass('animation_for_ajax_validation');
                return true;
              })
              .on('afterValidateAttribute', function (event, attribute) {
                if (attribute.id !== 'card-card_number') return true;
                $('#card-card_number').removeClass('animation_for_ajax_validation');
                return true;
              })
              .on('ajaxComplete', function (event, xhr) {
                if (!isApiAvailable) return true;

                <?php foreach ($model->getActiveCurrencies() as $currency): ?>
                  $('#currencies-hidden-checkbox-<?= $currency ?>-<?= $ps ?>').prop('checked', true);
                <?php endforeach ?>

                var cardDataRaw = xhr.responseJSON.hasOwnProperty('card-_carddata') ? xhr.responseJSON['card-_carddata'] : [];
                var cardData = cardDataRaw.hasOwnProperty(0) ? cardDataRaw[0] : {};

                var currencies = cardData.hasOwnProperty('currencies') ? cardData['currencies'] : [];

                if (currencies.length > 0) {
                  $('.field-card-card_number .help-block').html('<?= Yii::_t('partners.settings.card_currencies') ?>: ' + currencies.join(', ').toUpperCase());
                }

                $('.js-currencies').each(function () {
                  $(this).parent().addClass('hidden');
                });

                <?php foreach ($model->getActiveCurrencies() as $currency): ?>
                  if (currencies.indexOf('<?= $currency ?>') === -1) {
                    $('#currencies-hidden-checkbox-<?= $currency ?>-<?= $ps ?>').prop('checked', false);
                  }
                <?php endforeach ?>

                // Если кошелек заполнен, сервер не пометил его как невалидный и при этом не вернул список валют кошелька,
                // появится уведомление, что валюту необходимо выбрать вручную
                if ($('#card-card_number').inputmask('isComplete')
                  && !xhr.responseJSON.hasOwnProperty('card-card_number')
                  && currencies.length === 0
                ) {
                  $('.js-currencies-select-input-<?= $ps ?>').parent().parent().removeClass('hidden');
                  $('.js-currencies-select-input-<?= $ps ?>').parent().removeClass('hidden');
                  notifyInit(null, '<?= Yii::_t('partners.payments.auto_currency_not_available') ?>');
                  isApiAvailable = false;
                } else {
                  if (currencies.length > 1) {
                    $('.js-currencies-select-input-<?= $ps ?>').parent().parent().removeClass('hidden');
                    $('.js-currencies-select-input-<?= $ps ?>:checked').each(function () {
                      $(this).parent().removeClass('hidden');
                    });
                  }
                }
                return true;
              });
              var localCurrency = '<?=$walletsHandler::LOCAL_CURRENCY ?>'
                , $input = $('.js-currencies-select-input-<?= $ps ?>');
              $input.on('change', function (e) {
                var $this = $(this);
                if ($this.val() == localCurrency) {
                  // Если выбрали локальную валюту, то сбрасывать все остальные
                  $input.not($this).prop('checked', false);
                } else {
                  // И наоборот, если выбрали любую нелокальную, то сбрасывать локальную
                  $('.js-currencies-select-input-<?= $ps ?>[value="' + localCurrency + '"]').prop('checked', false);
                }
              });
          })();
          <?php $this->registerJs(ob_get_clean()) ?>
        </script>
        <?php if (count($model->getActiveCurrencies()) > 1): ?>
          <div class="content__position_padding">
            <?php foreach ($model->getActiveCurrencies() as $currency) { ?>
          <?php
        // Видим ли чекбокс с валютой
        // Видима, если это редактирование + валюта правильной группы (локальная или глобальная, в зависимости от выбранных валют редактируемой карты)
        $visible = (!in_array($walletsHandler::LOCAL_CURRENCY, $availableCurrencies, true) && $currency != $walletsHandler::LOCAL_CURRENCY)
        && !$userWallet->isNewRecord;
        ?>

          <div class="checkbox checkbox-primary checkbox-inline <?php if(!$visible):?>hidden<?php endif;?>">
            <?= Html::checkbox('currency[]', in_array($currency, $availableCurrencies, true), ['id' => 'currencies-hidden-checkbox-' . $currency . '-' . $ps, 'class' => 'js-currencies js-currencies-select-input-' . $ps, 'value' => $currency]) ?>
            <label class="control-label" for="currencies-hidden-checkbox-<?= $currency ?>-<?= $ps ?>">
              <?= Yii::_t('partners.payments.' . $currency . '_name') ?>
            </label>
          </div>
          <?php } ?></div>
        <?php else: ?>
          <?= Yii::_t('partners.payments.currency_' . $currency) ?>
        <?php endif; ?>
      <?php } else if ($model instanceof Wire) { ?>
        <div class="content__position_padding">
          <?php if ($model->getWallet()->is_usd) { ?>
          <div class="checkbox checkbox-primary checkbox-inline">
            <?= Html::checkbox('currency[]', $walletsHandler->showCurrency('usd', $userWallet->isNewRecord, $availableCurrencies), ['id' => 'currencies-hidden-checkbox-usd-' . $ps, 'value' => 'usd']) ?>
            <label class="control-label" for="currencies-hidden-checkbox-usd-<?= $ps ?>">
              <?= Yii::_t('partners.payments.usd_name') ?>
            </label>
          </div>
          <?php } ?>
          <?php if ($model->getWallet()->is_eur) { ?>
          <div class="checkbox checkbox-primary checkbox-inline">
            <?= Html::checkbox('currency[]', $walletsHandler->showCurrency('eur', $userWallet->isNewRecord, $availableCurrencies), ['id' => 'currencies-hidden-checkbox-eur-' . $ps, 'value' => 'eur']) ?>
            <label class="control-label" for="currencies-hidden-checkbox-eur-<?= $ps ?>">
              <?= Yii::_t('partners.payments.eur_name') ?>
            </label>
          </div>
          <?php } ?>
        </div>
        <script>
          <?php ob_start() ?>
          (function() {
            var isApiAvailable = true;

            // Автоматическое заполнение полей Wire при первой загрузке формы
            var help = [];
            if ($('#wire-swift_code').val()) {
              help.push('<?= $model->getAttributeLabel('swift_code')?>: ' + $('#wire-swift_code').val());
            }
            if ($('#wire-bank_county').val()) {
              help.push('<?= $model->getAttributeLabel('bank_county')?>: ' + $('#wire-bank_county').val());
            }
            if ($('#wire-bank_address').val()) {
              help.push('<?= $model->getAttributeLabel('bank_address')?>: ' + $('#wire-bank_address').val());
            }
            $('.field-wire-iban_code .help-block').html('<span id="wire-bank-data-help">' + help.join('<br>') + '</span>');


            $('#walletSettings-<?= $ps ?>:not(.wire_bind)').addClass('wire_bind')
              .on('beforeValidateAttribute', function (event, attribute) {
                if (attribute.id !== 'wire-iban_code') return true;
                $('#wire-iban_code').addClass('animation_for_ajax_validation');
                return true;
              })
              .on('afterValidateAttribute', function (event, attribute) {
                if (attribute.id !== 'wire-iban_code') return true;
                $('#wire-iban_code').removeClass('animation_for_ajax_validation');
                return true;
              })
              .on('ajaxComplete', function (event, xhr) {
                if (!isApiAvailable) {
                  return true;
                }

                var data = xhr.responseJSON.hasOwnProperty('wire-_bankdata') ? xhr.responseJSON['wire-_bankdata'] : [];
                var bankAcc = data.hasOwnProperty(0) ? data[0] : [];

                <?php // IBAN введен, IBAN прошел валидацию на сервере,
                // но скрипт не вернул информацию о кошельке (даже пустого массива), что означает, что API недоступно ?>
                if (
                  $('#wire-iban_code').val().trim() !== '' // IBAN заполнен
                  && !xhr.responseJSON.hasOwnProperty('wire-iban_code') // IBAN код прошел валидацию
                  && !xhr.responseJSON.hasOwnProperty('wire-_bankdata') // Не определена информация о банке
                ) {
                  $('.field-wire-swift_code, .field-wire-bank_county, .field-wire-bank_address').addClass('show').slideDown(100);
                  $('#wire-bank-data-help').remove();
                  notifyInit(null, '<?= Yii::_t('partners.payments.auto_bank_data_not_available') ?>');
                  isApiAvailable = false;
                  return true;
                }

                if ($("input:radio[name='Wire[account_type]']:checked").val() === 0) {
                  $('#wire-swift_code').val('');
                  $('#wire-bank_county').val('');
                  $('#wire-bank_address').val('');
                }

                var help = [];
                // Автоматическое заполнение полей Wire после валидации
                if (bankAcc.hasOwnProperty('swift_code') && bankAcc.swift_code) {
                  $('#wire-swift_code').val(bankAcc.swift_code);
                  help.push('<?= $model->getAttributeLabel('swift_code')?>: ' + bankAcc.swift_code);
                }
                if (bankAcc.hasOwnProperty('bank_county') && bankAcc.bank_county) {
                  $('#wire-bank_county').val(bankAcc.bank_county);
                  help.push('<?= $model->getAttributeLabel('bank_county')?>: ' + bankAcc.bank_county);
                }
                if (bankAcc.hasOwnProperty('bank_address') && bankAcc.bank_address) {
                  $('#wire-bank_address').val(bankAcc.bank_address);
                  help.push('<?= $model->getAttributeLabel('bank_address')?>: ' + bankAcc.bank_address);
                }

                var $helpBlock = $('.field-wire-iban_code .help-block'),
                  originalError = typeof xhr.responseJSON['wire-iban_code'] !== 'undefined' ? xhr.responseJSON['wire-iban_code'].join('<br>') : null,
                  errors = help.join('<br>');
              if (originalError) {
                  errors = !errors ? originalError : originalError + '<br>' + errors;
                }
                $helpBlock.html(errors);

                return true;
              });
          })();
        <?php $this->registerJs(ob_get_clean()) ?>
        </script>
      <?php } else if ($model instanceof WebMoney) { ?>
          <script>
            <?php ob_start() ?>
            // Автоматическое определение валют webmoney
            function updateWebmoneyCurrency() {
              var wallet = $('#webmoney-wallet').val();
              var walletLetter = wallet.charAt(0);
              <?php
              $currenciesMap = [];
              if ($model->getWallet()->is_rub) {
                $currenciesMap[] = 'R: \'rub\'';
              }
              if ($model->getWallet()->is_usd) {
                $currenciesMap[] = 'Z: \'usd\'';
              }
              if ($model->getWallet()->is_eur) {
                $currenciesMap[] = 'E: \'eur\'';
              }
              ?>
              var currenciesMap = {<?=implode(', ', $currenciesMap)?>};

              var currency = (typeof currenciesMap[walletLetter] !== 'undefined' ? currenciesMap[walletLetter] : '');

              $('#wallet-currency-<?= $ps ?>').val(currency);
            }

            $('body').on('change', '#webmoney-wallet', updateWebmoneyCurrency);
            updateWebmoneyCurrency();
            <?php $this->registerJs(ob_get_clean()) ?>
          </script>
        <?= Html::hiddenInput('currency', null, ['id' => 'wallet-currency-' . $ps]) ?>
      <?php } ?>
    </div>

  <div class="content__position">
      <div class="form-buttons">
        <?= Html::submitButton(
          Yii::_t('partners.wallets-manage.submit_' . ($userWallet->isNewRecord ? 'create' : 'update') . '_' . $paysystemCode),
          [
            'id' => $submitButtonId,
            'class' => 'btn btn-success' . ($isLocked ? ' disabled' : ''),
            'disabled' => $isLocked
          ]
        ) ?>

        <?php if (!$isLocked): ?>
          <?php $deleteConfirm = Yii::_t('partners.payments.wallet-delete-confirm') ?>
          <?= !$userWallet->isNewRecord ? Html::button(Yii::_t('partners.payments.delete_wallet'), [
            'onclick' => /** @lang JavaScript */
              "yii.confirm('$deleteConfirm', function() { 
                  PaysystemWallets.remove('{$userWallet->wallet_type}', '{$userWallet->id}');
              })",
            'class' => 'pull-right btn delete-wallet btn-default' . ($isLocked ? ' disabled' : ''),
            'disabled' => $isLocked
          ]) : '' ?>
        <?php endif ?>
      </div>
  </div>

<?php AjaxActiveForm::end() ?>

<?php $this->registerJs("
    $('[data-toggle=tooltip]').tooltip({container:'body'});
"); ?>