<?php

use yii\helpers\Html;
use yii\helpers\Url;
use mcms\common\widget\Select2;
use mcms\common\form\AjaxActiveForm;

/**
 * @var $this yii\base\View
 */

?>

<?php $form = AjaxActiveForm::begin([
  'id' => 'payments-export-form',
  'action' => ['export'],
  'method' => 'POST',
  'validationUrl' => ['export-validate'],
  'validateOnChange' => false,
  'validateOnBlur' => false,
  'ajaxSuccess' => 'function(data) {$("#payments-export-form").trigger("getExportLink", data);}',
  'options' => [
    'data-pjax' => 0,
    'data-link-text' => Yii::_t('export.export-link'),
  ]
]); ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>

  <div class="modal-body">
    <div class="form-group has-error hidden" id="no-payments-message">
      <strong class="control-label"><?= Yii::_t('export.error-no-payments'); ?></strong>
    </div>
    <div class="form-group <?php if (empty($exportModel->prevLink)): ?>hidden<?php endif ?>" id="export-link">
      <a class="control-label" href="<?= $exportModel->prevLink ?>"><?= Yii::_t('export.export-prev-link'); ?></a>
    </div>

  <?= $form->field($exportModel, 'status_ids')->widget(Select2::class, [
    'data' => $exportModel->getStatusesList(),
    'options' => [
      'multiple' => true,
    ]
  ]); ?>

  <?= $form->field($exportModel, 'wallet_ids')->widget(Select2::class, [
    'data' => $exportModel->getWalletTypesList(),
    'options' => [
      'multiple' => true,
    ]
  ]); ?>
</div>

  <div class="modal-footer">
    <div class="row">
      <div class="col-md-12">
        <?= Html::submitButton(
          Yii::_t('export.export'),
          ['class' => 'btn btn-success']
        ) ?>
      </div>
    </div>
  </div>

<?php AjaxActiveForm::end(); ?>

<?php
$this->registerJs('$(function() {
    $(document).trigger("mcms.payments.export.modal")
  })');