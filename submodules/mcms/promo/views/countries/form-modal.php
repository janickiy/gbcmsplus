<?php

use mcms\common\widget\modal\Modal;
use yii\helpers\Html;
use mcms\common\form\AjaxActiveForm;

/** @var \mcms\promo\models\Country $model */
?>

<?php $form = AjaxActiveForm::begin([
  'action' => $model->isNewRecord ? ['/promo/countries/create-modal'] : ['/promo/countries/update-modal', 'id' => $model->id],
  'ajaxSuccess' => Modal::ajaxSuccess('#countriesGrid'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $model->name ? : Yii::_t('promo.countries.create') ?></h4>
</div>

<div class="modal-body">
  <?= $form->field($model, 'name'); ?>
  <?= $form->field($model, 'code'); ?>
  <?= $form->field($model, 'currency') ?>
  <?= $form->field($model, 'local_currency')->label(Yii::_t('promo.countries.attribute-local_currency')) ?>
  <?= $form->field($model, 'status')->dropDownList($model->getStatuses(), ['prompt' => Yii::_t('app.common.not_selected')]) ?>
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


