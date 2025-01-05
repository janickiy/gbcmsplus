<?php

use mcms\payments\components\widgets\assets\PartnerAwaitingPaysums;
use rgk\utils\assets\CookiesAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/** @var array $byWallets */
/** @var array $totalsByCurrency */
PartnerAwaitingPaysums::register($this);
CookiesAsset::register($this);
$urlToGetBalance = Url::to(['/payments/payment-systems-api/get-balance']);
$urlToGetPartnerBalances = Url::to(['/payments/payments/get-balances']);
$js = '';
?>

<div class="partner-awaiting-paysums">
  <div class="total-payments">
    <div class="total-payments__title">
      <i class="fa fa-hourglass-half"></i>
      <?= Yii::_t('payments.user-payments.total_payments'); ?>
    </div>
    <div class="total-payments__list">
      <?php foreach (['rub', 'usd', 'eur'] as $currency) { ?>
        <?php $value = ArrayHelper::getValue($totalsByCurrency, $currency, 0); ?>
        <div class="total-payments__item">
          <span class="total-payments__item__value">
            <span title="<?= Yii::_t('payments.user-payments.amount_to_pay'); ?>">
              <?= Yii::$app->formatter->asDecimal($value, 2) ?>
            </span> <span id="ballances-<?=$currency?>" title="<?= Yii::_t('payments.user-payments.active_partners_balance'); ?>">
              (...)
            </span>
          </span> <span class="total-payments__item__currency"><?= $currency ?></span>
        </div>
      <?php } ?>
    </div>
  </div>
  <div class="current-balances">
    <div class="current-balances__title">
      <span class="current-balances__toggle">
        <i class="icon-payments"></i>
        <?= Yii::_t('payments.user-payments.current_balances'); ?>
        <i class="current-balances__toggle__icon"></i>
      </span>
    </div>
    <div class="current-balances__list">
      <?php
      $awaitingSumsCount = count($byWallets);
      $lastRowItemsCount = $awaitingSumsCount - floor($awaitingSumsCount / 4) * 4;
      $itemCount = 0;
      foreach ($byWallets as $walletData) { ?>
        <?php $isBalanceAvail = ArrayHelper::getValue($walletData, 'isBalanceAvailable');?>
        <?php $balanceFromCache = ArrayHelper::getValue($walletData, 'balanceFromCache');?>
        <?php $awaitingSum = ArrayHelper::getValue($walletData, 'awaitingSum', 0);?>
        <?php $apiId = ArrayHelper::getValue($walletData, 'apiId');?>

        <?php if ($isBalanceAvail && $balanceFromCache === null) {
          // Тащим из аякса только если null в кэше
          $js .= "updateApiBalance($apiId);";
        } ?>

        <div
          class="current-balances__item<?= ($itemCount > 3) ? ' not-first' : '' ?><?= ($awaitingSumsCount - $itemCount <= $lastRowItemsCount || $lastRowItemsCount == 0) ? ' last' : '' ?>
          <?php // если выплаты в ожид. = 0, то сперва прячем блок. И если пришел непустой баланс, то показываем блок. Иначе так и не показываем. ?>
          <?= $balanceFromCache===null && $isBalanceAvail && !$awaitingSum ? 'hidden' : ''?>"
        >
          <div class="current-balances__item__wallet">
            <?php if ($isBalanceAvail) { ?>
              <div class="current-balances__item__wallet__value">
                <i class="icon-payments"></i>
                <span id="api<?= $apiId ?>" data-api-id="<?= $apiId ?>">
                  <?= $balanceFromCache !== null ? Yii::$app->formatter->asDecimal($balanceFromCache, 2) : '...' ?>
                </span>
              </div>
            <?php } ?>
          </div>
          <div class="current-balances__item__wallet__awaiting">
            <i class="fa fa-hourglass-half"></i>
            <?= Yii::$app->formatter->asDecimal($awaitingSum, 2) ?>
          </div>
          <div class="current-balances__item__name"><?= $walletData['walletName'] ?> <?= $walletData['currency']?></div>
        </div>
        <?php $itemCount++; ?>
      <?php } ?>
    </div>
  </div>
</div>
<?php $js .= <<<JS
$.ajax({
    url: '$urlToGetPartnerBalances',
    success: function(data) {
      var rub = !data.rub ? '' : '(' + rgk.formatter.asCurrency(data.rub) + ')';
      var usd = !data.usd ? '' : '(' + rgk.formatter.asCurrency(data.usd) + ')';
      var eur = !data.eur ? '' : '(' + rgk.formatter.asCurrency(data.eur) + ')';
      
      $('#ballances-rub').html(rub);
      $('#ballances-usd').html(usd);
      $('#ballances-eur').html(eur);
    },
    fail: function() {
      $('#ballances-rub').html('');
      $('#ballances-usd').html('');
      $('#ballances-eur').html('');
    }
  });

function updateApiBalance(apiId) {
  $.ajax({
    url: '$urlToGetBalance',
    data: {id: apiId},
    success: function(data) {
      var value = ((data || {}).data || {}).formatted;
      if (value) {
        $('#api'+apiId)
          .text(value)
          .closest('.current-balances__item').removeClass('hidden'); // убираем класс тем блокам, которых сумма awaiting = 0
      } else {
        $('#api'+apiId).closest('.current-balances__item__wallet__value').remove();
      }
    }
  });
}

updateAllApiBalances = function () {
  $('.current-balances__item__wallet__value span[data-api-id]').each(function(){
    var apiId = $(this).data('api-id');
    updateApiBalance(apiId);
  });
};

initAwaitingCollapse();
JS;
 ?>
<?php $this->registerJS($js, $this::POS_LOAD); ?>