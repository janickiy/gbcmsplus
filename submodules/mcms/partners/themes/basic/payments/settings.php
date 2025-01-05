<?php
use mcms\common\form\AjaxActiveForm;
use mcms\partners\assets\FinanceAsset;
use mcms\partners\assets\PaymentsAsset;
use mcms\payments\models\UserWallet;
use mcms\payments\models\wallet\AbstractWallet;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\Pjax;

/** @var $walletsHandler \mcms\partners\components\WalletsDisplayHandler */
/** @var $currencies array */
/** @var $availableCurrencies array */
/** @var $balance float */
/** @var $currency string */
/** @var $convertedBalance array */
/** @var mcms\payments\models\UserPaymentSetting $userPaymentSettings */
/** @var boolean $haveMoney есть ли остаток в текущей валюте */
/** @var boolean $isCurrencyChanged незавершенная смена валюты */
/** @var \yii\web\View $this */
/** @var bool $hasWalletDetailsAccess */
/** @var bool $isLocked */

// Если отлавливать клик, то хэндлер почему-то вызывается два раза, поэтому отлавливается change
$this->registerJs(<<<JS
$(document).on('change', '.partners-merchant input[name=merchant]', function() {
  PaysystemsList.showSettings($(this).data('paysystem-id'));  
});
JS
);

// ключ массива - тип платежки
$icons = [
  1 => '<i class="merchant-icon icon-webmoney1"></i>',
  2 => '<span class="merchant-icon icon-yandex1">
              <span class="path1"></span>
              <span class="path2"></span>
              <span class="path3"></span>
            </span>',
  7 => '<span class="merchant-icon icon-wire1">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
            <span class="path5"></span>
            <span class="path6"></span>
            <span class="path7"></span>
            <span class="path8"></span>
            <span class="path9"></span>
            <span class="path10"></span>
            <span class="path11"></span>
            <span class="path12"></span>
            <span class="path13"></span>
        </span>',
  8 => '<span class="merchant-icon icon-wire1">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
            <span class="path5"></span>
            <span class="path6"></span>
            <span class="path7"></span>
            <span class="path8"></span>
            <span class="path9"></span>
            <span class="path10"></span>
            <span class="path11"></span>
            <span class="path12"></span>
            <span class="path13"></span>
        </span>',
  3 => '<i class="merchant-icon icon-epayments12"></i>',
  4 => '<i class="merchant-icon icon-neteller"></i>',
  5 => '<span class="merchant-icon icon-paypal1">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
        </span>',
  9 => '<span class="merchant-icon icon-epayservice">
          <img src="/img/epayservice.svg" alt="">
        </span>',
  6 => '<span class="merchant-icon icon-paxum1">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
        </span>',
  10 => '<span class="merchant-icon icon-card1">
          <span class="path1"></span>
          <span class="path2"></span>
          <span class="path3"></span>
          <span class="path4"></span>
          <span class="path5"></span>
          <span class="path6"></span>
          <span class="path7"></span>
          <span class="path8"></span>
          <span class="path9"></span>
          <span class="path10"></span>
        </span>',
  11 => '<span class="merchant-icon icon-ie1">
          <span class="path1"></span>
          <span class="path2"></span>
          <span class="path3"></span>
          <span class="path4"></span>
          <span class="path5"></span>
          <span class="path6"></span>
        </span>',
  12 => '<span class="merchant-icon icon-le1">
          <span class="path1"></span>
          <span class="path2"></span>
          <span class="path3"></span>
          <span class="path4"></span>
          <span class="path5"></span>
        </span>',
  13 => '<i class="merchant-icon icon-qiwi1"></i>',
  14 => '<span class="merchant-icon icon-capitalist1">
            <span class="path1"></span>
          <span class="path2"></span>
          <span class="path3"></span>
          <span class="path4"></span>
          <span class="path5"></span>
          <span class="path6"></span>
</span>',
  15 => '<span class="merchant-icon icon-usdt1">
            <span class="path1"></span>
          <span class="path2"></span>
</span>',
];
FinanceAsset::register($this);

$isLockedCurrency = !($userPaymentSettings->canChangeCurrency());

/** @var string $walletFormUrl Урл, по которому откроется форма добавления/редактирования кошелька */
$walletFormUrl = Url::to(['payments/wallet-form']);
$walletListUrl = Url::to(['payments/wallets-list', 'type' => '']);
$changeCurrencyUrl = Url::to(['payments/change-currency']);

$userWallets = Yii::$app->getModule('payments')->api('userWallet')->getUserWallets($availableCurrencies);
$userWalletsByType = ArrayHelper::map(is_array($userWallets) ? $userWallets : [], 'wallet_type', 'id');
$userWalletIds = ArrayHelper::getColumn(is_array($userWallets) ? $userWallets : [], 'wallet_type');
$formatter = Yii::$app->formatter;
/**
 * @var array $juridicalPercents
 * для вывода в текстовом блоке процент юрлиц, ип. Отобразится в виде "2-3" или просто "2"
 */
$juridicalPercents = [];

?>

<div class="container-fluid">
  <script>
  FINANCE_PARAMETERS_WALLET_URL = '<?= $walletFormUrl ?>';
  DELETE_WALLET_URL = '<?= Url::to(['payments/delete-wallet']) ?>';
  WALLET_LIST_URL = '<?= $walletListUrl ?>';
  PAYSYSTEM_SETTINGS_URL = '<?= Url::to(['payments/settings']) ?>';
  CHANGE_CURRENCY_URL = '<?= $changeCurrencyUrl ?>';
  TITLE_ACTION_TEXT_CANCEL = '<?= Yii::_t('partners.settings.cancel_currency_change') ?>';
  CURRENT_CURRENCY = '<?= $currency ?>';
  CONVERT_BALANCE = <?= Json::encode($convertedBalance)?>;
  IS_PASSWORD_CONFIRMED = <?= $hasWalletDetailsAccess ? 'true' : 'false' ?>;
  SETTINGS_WALLET_TYPES_URL = '<?= Url::to(['settings-wallet-types', 'currency' => '']) ?>';
  IS_SETTINGS_PAGE = true;
</script>
  <div class="row">
    <div class="col-xs-6 mw650 right-col">
      <div class="bgf profile profile-finance">
        <div class="currency-wrapper">
          <div class="title title_with-action">
            <h2><?= Yii::_t('partners.payments.cabinet_currency')?></h2>
            <div id="currencyChangeAction"
                 class="title-action<?= $isLockedCurrency || $isCurrencyChanged ? ' disabled' : null ?><?= $balance == 0 ? ' change-currency' : null ?>"
                 <?= $isLockedCurrency ? ' title="' . ($balance >= 0 ? Yii::_t('partners.settings.currency_change_forbidden') : Yii::_t('partners.settings.currency_change_balance_negative_forbidden')) . '"' : null ?>
                 data-toggle='tooltip'
                 data-placement='bottom'>
              <i class="title-action__icon icon-change"></i>
              <span class="title-action__text"><?= Yii::_t('partners.settings.change-currency') ?></span>
            </div>
          </div>
          <div class="content__position">
            <div class="form-group partners-currency radio__filter radio-buttons">
              <div>
                <?php foreach ($currencies as $currencyCode => $currencyLabel) { ?>
                    <?php $active = $userPaymentSettings->currency == $currencyCode; ?>
                    <label class="<?= $active ? 'active' : ''?> <?=$currencyCode?>_payment">
                      <?= Html::radio('currency', $active, [
                        'value' => $currencyCode
                      ])?><?= $currencyLabel ?>
                    </label>
                <?php } ?>
              </div>
              <div class="help-block"></div>
            </div>
          </div>
        </div>
        <div class="title hidden-xs">
          <h2><?= Yii::_t('partners.payments.available_wallet_types')?></h2>
        </div>
        <div class="radio-buttons">
          <?php // TODO Адовый костыль. Ниже два одинаковых блока. Нужно избавится от дублирования (и не запороть сворачивание локальных ПС). Есть идеи как это сделать, спрашивай. Костыль с JS потеряет актуальность после рефакторинга ?>
          <?php if ($walletsHandler->hasLocalWallets()): ?>
            <?php if (!$walletsHandler->showLocalFirst && $walletsHandler->hasInternationalWallets()): ?>
              <?php // TRICKY Если локальные системы нужно скрыть, показываем их после международных ?>
              <?php $this->registerJs('
                  var pdiv = $("#international-p-s");
                  pdiv.insertBefore(pdiv.prev());
              ') ?>
            <?php endif;?>
            <div id="local-p-s" class="local_wallets local_wallets_first">
              <div class="subtitle" id="local-wallets-title" style="cursor: pointer" data-first="<?= json_encode($walletsHandler->showLocalFirst) ?>">
                <h5><?= Yii::_t('partners.settings.local') ?> <span id="local-wallets-title-caret"></span></h5>
              </div>
              <div id="local-wallets" class="content__position position-xs partners-merchant-container local-wallets">
                <ul class="row partners-merchant radio__filter">
                  <?php foreach ($walletsHandler->localWallets as $wallet): ?>
                    <?php Yii::debug($wallet);
                    $resellerPercent = $wallet->calcResellerPercent(Yii::$app->user->id) ?>
                    <li class="col-xs-4">
                      <?php
                      echo Html::beginTag('label', [
                        'class' => in_array($wallet->getType(), $userWalletIds) ? 'filled' : '',
                      ]);
                      echo Html::beginTag('div', [
                        'class' => 'merchant-percent merchant-percent_' .
                          ($resellerPercent > 0 ? 'positive' :
                            ($resellerPercent == 0 ? '' : 'negative'))
                      ]);
                      echo ($resellerPercent > 0 ? '+' : '') . ($resellerPercent + 0) . '%';
                      echo Html::endTag('div');
                      echo ArrayHelper::getValue($icons, $wallet->getType());
                      echo Html::input('radio', 'merchant', $wallet->getType(), [
                        'data-paysystem-id' => $wallet->getType()
                      ]);
                      echo $wallet::getName();
                      echo Html::endTag('label'); ?>
                    </li>
                  <?php endforeach ?>
                </ul>
              </div>
            </div>
          <?php endif ?>
          <?php if ($walletsHandler->hasInternationalWallets()): ?>
            <div id="international-p-s">
              <div class="subtitle">
                <h5><?= Yii::_t('partners.settings.international') ?></h5>
              </div>
              <div class="content__position position-xs partners-merchant-container">
                <ul class="row partners-merchant radio__filter">
                  <?php foreach ($walletsHandler->internationalWallets as $wallet): ?>
                    <?php $resellerPercent = $wallet->calcResellerPercent(Yii::$app->user->id) ?>
                      <li class="col-xs-4">
                      <?php
                      echo Html::beginTag('label', [
                        'class' => in_array($wallet->getType(), $userWalletIds) ? 'filled' : '',
                      ]);
                      echo Html::beginTag('div', [
                        'class' => 'merchant-percent merchant-percent_' .
                          ($resellerPercent > 0 ? 'positive' :
                            ($resellerPercent == 0 ? '' : 'negative'))
                      ]);
                      echo ($resellerPercent > 0 ? '+' : '') . ($resellerPercent + 0) . '%';
                      echo Html::endTag('div');
                      echo ArrayHelper::getValue($icons, $wallet->getType());
                      echo Html::input('radio', 'merchant', $wallet->getType(), [
                        'data-paysystem-id' => $wallet->getType()
                      ]);
                      echo $wallet::getName();
                      echo Html::endTag('label'); ?>
                    </li>
                  <?php endforeach ?>
                </ul>
              </div>
            </div>
          <?php endif ?>
        </div>
      </div>
    </div>
    <div class="col-xs-6 left-col">
      <div id="paysystems-settings-help" class="bgf profile profile-finance wallet_settings active">
        <div class="title">
          <h2><?= Yii::_t('partners.payments.choose_paytype') ?></h2>
        </div>
        <div class="content__position">
          <p><?= Yii::_t('partners.payments.choose_paytype_info_1') ?></p>
        </div>
        <div class="content__position danger_message">
          <i class="icon-ok_comment"></i>
          <?php
          $juridicalPercents = array_unique($juridicalPercents);
          sort($juridicalPercents);
          ?>
          <p><?= Yii::_t('partners.payments.choose_paytype_info_recommend', [
              ':percent' => implode('-', $juridicalPercents)
            ]) ?></p>
        </div>
      </div>
          <?php foreach ($walletsHandler->systemWallets as $wallet) { ?>
            <?php Pjax::begin(['id' => 'paysystem-settings-' . $wallet->getType(), 'options' => [
              'data-paysystem-id' => $wallet->getType(),
              'class' => 'js-paysystem-settings wallet_settings form',
              'style' => Yii::$app->request->isPjax ? null : 'display: none',
            ]]) ?>
            <?php
            /** @var UserWallet $userWallet */
            $userWallets = Yii::$app->controller->getWalletModels($wallet->getType(), $availableCurrencies);
            $userWallet = is_array($userWallets) ? reset($userWallets) : $userWallets;
            $walletAccount = $userWallet->getAccountObject();
            ?>

            <?php Pjax::begin(['id' => 'paysystem-wallets-' . $wallet->getType(), 'options' => ['style' => Yii::$app->request->isPjax ? null : 'display: none']]) ?>
            <?= $this->render('wallet-list', [
              'model' => $walletAccount,
              'userWallet' => $userWallet,
              'walletType' => $wallet->getType(),
              'wallets' => $userWallets,
              'isLocked' => $isLocked,
            ]) ?>
            <?php Pjax::end() ?>

            <?php Pjax::begin(['id' => 'paysystem-wallet-form-' . $wallet->getType(), 'options' => [
              'class' => 'js-wallet-form-container bgf profile profile-finance wallet_settings active',
              'style' => Yii::$app->request->isPjax ? null : 'display: none',
            ],
            ]) ?>
            <?php $newUserWallet = (new UserWallet(['user_id' => Yii::$app->user->id, 'wallet_type' => $wallet->getType()])); ?>
            <?= $this->render('wallet-form', [
              // TODO Сделать параметры под создание нового кошелька
              'availableCurrencies' => $availableCurrencies,
              'model' => $newUserWallet->getAccountObject(),
              'userWallet' => $newUserWallet,
              'walletType' => $wallet->getType(),
              'wallets' => $userWallets,
              'walletsHandler' => $walletsHandler,
              'isLocked' => $isLocked,
            ]) ?>
            <?php Pjax::end() ?>
            <?php Pjax::end() ?>
          <?php } ?>
    </div>
  </div>
</div>
<!-- Затемненный лайоут при выборе валют -->
<div class="fade-layout"></div>


<!-- Modal (подтверждение смены валюты) -->
<div class="modal fade currency-change-modal" id="currencyChangeModal" tabindex="-1" role="dialog" >
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <?php $form = AjaxActiveForm::begin([
        'action' => $changeCurrencyUrl,
        /** помечаем кошелек заполненным */
        'ajaxSuccess' => new JsExpression(
            /** @lang JavaScript */ '
          function(data) {
            $("#currencyChangeModal").modal("hide");
            if (data.success) {
              $("#currencyChangeAction").hide();
            }
          }
        '),
      ]); ?>
      <input type="hidden" name="newCurrency" id="newCurrency" />
      <div class="modal-header">
        <button type="button" class="close" aria-label="Close"><i class="icon-cancel_4"></i></button>
        <h4 class="modal-title">
          <i class="icon-danger"></i>
          <?= Yii::_t('partners.settings.attention') ?>
        </h4>
      </div>
      <div class="modal-body">
        <?php if($haveMoney): ?>
        <p><?= Yii::_t('partners.settings.ballance-not-empty') ?></p>
        <?php endif; ?>
        <p><?= Yii::_t('partners.settings.shure-chenge-currency') ?>: <span class="whs-nw"><?= $formatter->asPrice($balance, $currency)?></span> <i class="icon-next_3"></i> <span id="convertedBalance" class="whs-nw"></span></p>
      </div>
      <div class="modal-footer modal-footer_tac-mobile">
        <button id="acceptCurrencyChange" type="submit" name="convert" value="0" class="btn btn-success pull-left-desktop mb-mobile"><?= Yii::_t('partners.settings.withdraw_later') ?></button>
        <button id="convertCurrencyChange" type="submit" name="convert" value="1" class="btn btn-success pull-left-desktop mb-mobile"><?= Yii::_t('partners.settings.convert_now') ?></button>
        <button id="cancelCurrencyChange" type="button" class="btn btn-default" ><?= Yii::_t('partners.settings.cancel') ?></button>
      </div>

      <?php AjaxActiveForm::end()?>

    </div>
  </div>
</div>

<?= $this->render('_password_modal') ?>