<?php
use mcms\common\helpers\ArrayHelper;
use mcms\payments\models\UserWallet;
use mcms\payments\models\wallet\AbstractWallet;
use mcms\payments\models\wallet\Wallet;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var AbstractWallet $model */
/** @var UserWallet $userWallet */
/** @var bool $isLocked */
/** @var array $wallets */
/** @var int $walletType */
/** @var  $availableCurrencies array */
$paysystemCode = $userWallet->walletType->code;

$viewUrl = [
  'payments/wallet-form',
  'walletId' => $userWallet->id,
  'type' => $model->getType(),
  'currency' => $model->getSelectedCurrency()
];
$walletAddUrl = "&type=$walletType&new=1";
$walletFormAction = array_merge($viewUrl, ['new' => $userWallet->isNewRecord]);
$walletFormAction = Url::to($walletFormAction);
$viewUrl = Url::to($viewUrl);
$walletListUrl = Url::to(['payments/wallets-list', 'type' => $model->getType()]);
$submitButtonId = 'submit-partner-wallet';

$currencyIcons = [
  'rub' => '<i class="icon-ruble"></i>',
  'usd' => '$',
  'eur' => '<i class="icon-euro"></i>',
];
?>

<?php if (is_array($wallets)) { ?>
<div class="bgf profile profile-finance">
    <div class="wallet_settings active card-addition">
        <div class="title title_with-action">
            <h2><?= Yii::_t('partners.wallets-manage.list_' . $paysystemCode) ?></h2>
        </div>
      <?php foreach ($wallets as $wallet) { ?>
          <div class="card-addition__row">
            <?= $wallet->getAccountObject()->getIcon() ?>
              <a href="javascript:void(0)" class="card-addition__number"
                 onclick="PaysystemWalletForm.loadEdit(<?= $wallet->wallet_type ?>, <?= $wallet->id ?>)"
                 title="<?= Yii::_t('partners.payments.requisites_update') ?>">
                <?= $wallet->getAccountObject()->getUniqueValueProtected() ?: $wallet->walletTypeLabel ?>
              </a>
              <div class="card-addition__right-block">
                  <div class="card-addition__currencies">
                    <?php
                    $walletCurrencies = explode(',', $wallet->currency);
                    $walletType = $wallet->walletType;

                    foreach ($walletCurrencies as $currency) { ?>
                      <?= ArrayHelper::getValue($currencyIcons, $currency) ?>
                    <?php } ?>
                  </div>
                  <div class="card-addition__actions">
                      <a href="javascript:void(0)"
                         id="wallet-edit-button-<?= $wallet->id ?>"
                         onclick="PaysystemWalletForm.loadEdit(<?= $wallet->wallet_type ?>, <?= $wallet->id ?>)"
                         class="js-wallet-edit-button card-addition__action card-addition__action_edit"
                         title="<?= Yii::_t('partners.payments.requisites_update') ?>"
                      >
                          <i class="icon-edit"></i>
                      </a>

                    <?php if (!$isLocked): ?>
                      <?php $deleteConfirm = Yii::_t('partners.payments.wallet-delete-confirm') ?>
                      <?= Html::a('<i class="icon-delete"></i>', 'javascript:void(0)', [
                        'onclick' => /** @lang JavaScript */
                          "yii.confirm('$deleteConfirm', function() { 
                            PaysystemWallets.remove('{$wallet->wallet_type}', '{$wallet->id}');
                        })",
                        'class' => 'card-addition__action card-addition__action_delete',
                        'title' => Yii::_t('partners.payments.delete_wallet')
                      ]); ?>
                    <?php endif ?>
                  </div>
              </div>
          </div>
      <?php } ?>
    </div>
</div>
<?php  }else {?>
  <?php
  // Снимаем активность с платежки, если удален последний кошелек
  $this->registerJs("$('.profile-finance input[name=merchant]').filter('[value=" . $model->getType() . "]').closest('label').removeClass('filled');");
  ?>
    <span></span><?php // Если убрать этот тэг, после удаления последнего кошелька будет перезагружаться страница, так как pjax посчитает, что сервер ответил неверно ?>
<?php } ?>

<?php $this->registerJs('
    $("[data-toggle=tooltip]").tooltip({container:"body"});
'); ?>
