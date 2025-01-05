<?php
use mcms\common\form\AjaxActiveForm;
use mcms\common\helpers\Html;
use mcms\common\widget\modal\Modal;
use mcms\payments\assets\InvoiceFormAsset;

/** @var \mcms\payments\models\UserBalanceInvoice $model */
/** @var \mcms\payments\models\UserPaymentSetting $userPaymentSettings*/
?>

<?php $form = AjaxActiveForm::begin([
  'ajaxSuccess' => Modal::ajaxSuccess('#balance'),
]) ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>

  <div class="modal-body">
    <?php $amountLabel = $model->getAttributeLabel('amount'); ?>
    <?php if ($userPaymentSettings->canUseMultipleCurrenciesBalance()): ?>
      <?= $form->field($model, 'currency')->dropDownList($userPaymentSettings->currencyList) ?>
    <?php else: ?>
      <?php $amountLabel = $model->getAttributeLabel('amount') . " ({$userPaymentSettings->getCurrentCurrency()})"; ?>
      <?= $form->field($model, 'currency')->hiddenInput()->label(false) ?>
    <?php endif ?>
    <?= $form->field($model, 'amount')->label($amountLabel) ?>
    <?= $form->field($model, 'description')->textarea() ?>
  </div>

  <div class="modal-footer">
    <div class="row">
      <div class="col-md-12">
        <?= Html::submitButton(
          '<i class="fa fa-save"></i> ' . Yii::_t('app.common.Add'),
          ['class' => 'btn btn-success']
        ) ?>
      </div>
    </div>
  </div>
<?php AjaxActiveForm::end() ?>