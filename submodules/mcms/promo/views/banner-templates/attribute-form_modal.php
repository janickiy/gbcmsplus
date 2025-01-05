<?php

use mcms\common\widget\modal\Modal;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use mcms\common\multilang\widgets\input\InputWidget;
use mcms\common\form\AjaxActiveKartikForm;

/* @var $this yii\web\View */
/* @var $model mcms\promo\models\BannerTemplateAttribute */
/* @var $form yii\widgets\ActiveForm */
?>

<?php $form = AjaxActiveKartikForm::begin([
  'type' => ActiveForm::TYPE_HORIZONTAL,
  'ajaxSuccess' => Modal::ajaxSuccess('#templateAttributesContainer'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $model->isNewRecord ? $model::translate('create') : $model->name ?></h4>
</div>

<div class="modal-body">
  <?= $form->field($model, 'name')->widget(InputWidget::class, [
    'class' => 'form-control',
    'form' => $form
  ]) ?>
  <?= $form->field($model, 'code') ?>
  <?= $form->field($model, 'type')->dropDownList($model->getTypesLabels(), ['prompt' => ''  ]) ?>
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
