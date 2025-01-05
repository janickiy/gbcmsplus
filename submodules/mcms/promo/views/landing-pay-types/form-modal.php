<?php

use mcms\common\widget\modal\Modal;
use yii\helpers\Html;
use mcms\promo\models\LandingPayType;
use mcms\common\form\AjaxActiveForm;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var mcms\promo\models\LandingPayType $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<?php $form = AjaxActiveForm::begin([
  'action' => $model->isNewRecord ? ['/promo/landing-pay-types/create-modal'] : ['/promo/landing-pay-types/update-modal', 'id' => $model->id],
  'ajaxSuccess' => Modal::ajaxSuccess('#LandingPayTypesGrid'),
]); ?>
<div class="modal-header">
  <?= Html::button('<span aria-hidden="true">&times;</span>', ['class' => 'close', 'data-dismiss' => 'modal']); ?>
  <h4 class="modal-title"><?= $model->name ?: LandingPayType::translate('create') ?></h4>
</div>

<div class="modal-body">
  <?= $form->field($model, 'code')->textInput(['maxlength' => 100]) ?>
  <?= $form->field($model, 'name')->textInput(['maxlength' => 100]) ?>

  <?= $form->field($model, 'status')->dropDownList($model->getStatuses()) ?>

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


