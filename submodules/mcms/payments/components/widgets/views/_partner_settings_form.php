<?php
use kartik\widgets\Spinner;
use mcms\common\form\AjaxActiveForm;
use mcms\common\widget\modal\Modal;
use mcms\payments\components\widgets\PartnerSettings;
use mcms\payments\models\wallet\Wallet;
use mcms\payments\Module;
use mcms\payments\models\UserPaymentSetting;
use yii\helpers\Html;

/** @var \mcms\payments\models\UserPaymentSetting $model */
/** @var bool $canChangeWallet */
/** @var bool $modal */
$modal = !empty($modal);
$walletOptions = $canChangeWallet ? [] : ['disabled' => true];
?>
<?php $form = AjaxActiveForm::begin([
  'id' => 'user-payment-settings',
  'action' => [PartnerSettings::URL . 'update-partner-settings'],
  'ajaxComplete' => 'function(){$("#partner-settings form").dirtyForms("setClean");}',
  'ajaxSuccess' => $modal ? Modal::ajaxSuccess() : 'function() { $("#payments-settings-modal").modal("hide") }',
]) ?>
<?php if ($modal) : ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $this->title ?></h4>
</div>
<div class="modal-body">
<?php endif ?>

<?= $form->field($model, 'currency')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'wallet_type')->hiddenInput()->label(false) ?>

<?= Spinner::widget([
  'preset' => 'small',
  'options' => [
    'class' => 'hidden',
    'id' => 'user-payment-settings-container-loading']
]) ?>

<?= $form->field($model, 'visible_referral_percent')->textInput(['disabled' => true]) ?>


  <?php if ($modal) : ?>
</div>
  <div class="modal-footer">
    <div class="row">
      <div class="col-md-12">
        <?= Html::submitButton(
          '<i class="fa fa-save"></i> ' . ($model->isNewRecord ? Yii::_t('app.common.Create') : Yii::_t('app.common.Save')),
          ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
        ) ?>
      </div>
    </div>
  </div>
    <?php else : ?>
      <hr>
      <div class="clearfix">
        <?= Html::submitButton('<i class="fa fa-save"></i> ' .Yii::_t('app.common.Save'), ['class' => 'btn btn-primary pull-right']) ?>
      </div>
    <?php endif ?>
<?php AjaxActiveForm::end() ?>
