<?php

use mcms\common\widget\modal\Modal;
use yii\helpers\Html;
use mcms\common\form\AjaxActiveForm;

$id = 'streams';

?>

<?php $form = AjaxActiveForm::begin([
  'action' => $model->isNewRecord ? ['/promo/' . $id . '/create-modal'] : ['/promo/' . $id . '/update-modal', 'id' => $model->id],
  'ajaxSuccess' => Modal::ajaxSuccess('#' . $id . 'PjaxGrid'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $model->name ?></h4>
</div>

<div class="modal-body">
  <?= $form->errorSummary($model, ['class' => 'alert alert-danger']); ?>
  <?= $form->field($model, 'name'); ?>
  <?= $form->field($model, 'status')->dropDownList($model->statuses); ?>

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


