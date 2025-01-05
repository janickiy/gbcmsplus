<?php
use mcms\payments\models\wallet\AbstractWallet;
use mcms\payments\models\wallet\Wallet;
use mcms\payments\models\wallet\WalletForm;

/** @var \mcms\payments\models\UserWallet $model */
/** @var AbstractWallet $walletAccount */
/** @var array $walletOptions */

?>
<?php if (!$model->wallet_type) return;

if ($walletAccount && $model->wallet_type == $walletAccount->getType()) {
  foreach ($walletAccount->getForm($form, $model->id)->createAdminFormFields() as $field) {
    echo $field;
  }
  return;
}

$walletAccountEmpty = Wallet::getObject($model->wallet_type);
foreach ($walletAccountEmpty->getForm($form, $model->id)->createAdminFormFields() as $field) {
  echo $field;
}
?>
