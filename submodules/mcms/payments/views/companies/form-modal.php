<?php

use rgk\utils\widgets\modal\Modal;
use yii\helpers\Html;
use rgk\utils\widgets\form\AjaxActiveForm;

/** @var \mcms\payments\models\Company $model */
?>

<?php $form = AjaxActiveForm::begin([
  'action' => $model->isNewRecord ? ['/payments/companies/create'] : ['/payments/companies/update-modal', 'id' => $model->id],
  'isFilesAjaxUpload' => true,
  'ajaxSuccess' => Modal::ajaxSuccess('#companiesPjaxGrid'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $model->name ? : Yii::_t('payments.company.create') ?></h4>
</div>

<div class="modal-body">
  <?= $form->field($model, 'name'); ?>
  <?= $form->field($model, 'country'); ?>
  <?= $form->field($model, 'city'); ?>
  <?= $form->field($model, 'address'); ?>
  <?= $form->field($model, 'post_code'); ?>
  <?= $form->field($model, 'tax_code'); ?>

  <?= $form->field($model, 'logo')->fileInput(['accept' => 'image/*']); ?>

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
<?php AjaxActiveForm::end(); ?>
