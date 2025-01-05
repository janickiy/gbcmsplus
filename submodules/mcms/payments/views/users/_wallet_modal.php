<?php
use kartik\form\ActiveForm;
use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\helpers\Html;
use mcms\common\widget\modal\Modal;
use mcms\payments\Module;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var \yii\web\View $this */
/** @var \mcms\payments\models\UserWallet $model */
/** @var \mcms\payments\models\wallet\AbstractWallet $walletAccount */

$walletModalUrl = Url::to(['/payments/users/wallet-modal', 'id' => $model->id, 'userId' => $model->user_id]);

$onCurrencyChange = <<<JS
$.pjax.reload("#wallet-modal-form", {
  type: "post",
  url: "$walletModalUrl&currency=" + $(".currency_select").val(),
  push: false,
  replace: false
});
JS;


$onChange = <<<JS
$.pjax.reload("#wallet-modal-form", {
  type: "post",
  url: "$walletModalUrl",
  data: $("#currency-wallet").serialize(),
  push: false,
  replace: false
});
JS;

$this->registerJs($onChange);

?>

<?php Pjax::begin([
  'id' => 'wallet-modal-form',
  'enablePushState' => false,
  'enableReplaceState' => false,
]) ?>

<?php $form = AjaxActiveKartikForm::begin([
  'id' => 'currency-wallet',
  'ajaxSuccess' => Modal::ajaxSuccess('#user-payment-settings-pjax-block'),
  'options' => ['enctype' => 'multipart/form-data'],
]) ?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">
      <?php if (!$model->isNewRecord): ?>
        <?= $wallets[$model->wallet_type]; ?>
      <?php endif; ?>
    </h4>
</div>

<div class="modal-body">
  <?php if ($model->isNewRecord): ?>
    <?= $form->field($model, 'currency', ['validateOnChange' => false])->dropDownList($model->getCurrenciesDropdownItems(), [
      'class' => 'currency_select',
      'onchange' => $onCurrencyChange
    ]) ?>
    <?= $form->field($model, 'wallet_type', ['validateOnChange' => false])->dropDownList(
      $model->currency ? $model->getWalletDropDown($model->currency, true) : [],
      [
        'prompt' => Yii::_t('app.common.choose'),
        'onchange' => $onChange
      ]
    ) ?>
  <?php endif; ?>
  <?= $form->field($model, 'user_id')->hiddenInput()->label(false) ?>
  <?= $this->render('_wallet_fields', ['model' => $model, 'walletAccount' => $walletAccount, 'form' => $form, 'walletOptions' => []]) ?>
  <?= $form->field($model, 'is_autopayments')->checkbox(); ?>
  <?php if (Module::isUserCanVerifyWallets()) :?>
    <?= $form->field($model, 'is_verified')->checkbox(); ?>
  <?php endif ?>


</div>

<div class="modal-footer">
    <div class="row">
        <div class="col-md-12">
          <?= Html::submitButton(
            '<i class="fa fa-save"></i> ' . $model->id ? Yii::_t('app.common.Save') : Yii::_t('app.common.Add'),
            ['class' => 'btn btn-success']
          ) ?>
        </div>
    </div>
</div>
<?php AjaxActiveKartikForm::end() ?>
<?php Pjax::end() ?>
