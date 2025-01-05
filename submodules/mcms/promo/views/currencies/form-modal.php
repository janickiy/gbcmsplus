<?php

use mcms\common\multilang\widgets\multilangform\MultiLangForm;
use mcms\common\widget\modal\Modal;
use yii\helpers\Html;
use mcms\common\form\AjaxActiveKartikForm;
use yii\web\JsExpression;

$id = 'currencies';

?>

<?php $form = AjaxActiveKartikForm::begin([
  'action' => $model->isNewRecord ? ['/promo/' . $id . '/create-modal'] : ['/promo/' . $id . '/update-modal', 'id' => $model->id],
  'ajaxSuccess' => Modal::ajaxSuccess('#' . $id . 'PjaxGrid'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $model->name ? : Yii::_t('promo.' . $id . '.create') ?></h4>
</div>

<div class="modal-body">
  <?= MultiLangForm::widget([
    'model' => $model,
    'form' => $form,
    'attributes' => [
      'name' => [
        'type' => \kartik\builder\Form::INPUT_TEXT
      ],
    ]
  ])  ?>
  <?= $form->field($model, 'code'); ?>
  <?= $form->field($model, 'symbol'); ?>
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


