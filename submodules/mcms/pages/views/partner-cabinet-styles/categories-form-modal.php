<?php

use mcms\common\multilang\widgets\input\InputWidget;
use mcms\common\multilang\widgets\multilangform\MultiLangForm;
use mcms\common\widget\modal\Modal;
use yii\helpers\Html;
use mcms\common\form\AjaxActiveKartikForm;
use yii\web\JsExpression;


?>

<?php $form = AjaxActiveKartikForm::begin([
  'ajaxSuccess' => Modal::ajaxSuccess('#style-categories-pjax'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $this->title ?></h4>
</div>

<div class="modal-body">
  <?= $form->field($model, 'name')->widget(InputWidget::class, [
    'class' => 'form-control',
    'form' => $form,
  ]); ?>
  <?= $form->field($model, 'code'); ?>
  <?= $form->field($model, 'sort'); ?>
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

<?php AjaxActiveKartikForm::end(); ?>


