<?php

use mcms\common\widget\modal\Modal;
use yii\helpers\Html;
use mcms\promo\models\Source;
use mcms\common\form\AjaxActiveForm;

/**
 * @var yii\web\View $this
 * @var Source $model
 * @var yii\widgets\ActiveForm $form
 * @var $currency
 */
?>

<?php $form = AjaxActiveForm::begin([
  'action' => ['arbitrary-sources/disable-modal', 'id' => $model->id],
  'ajaxSuccess' => Modal::ajaxSuccess('#arbitrary-sources-pjax'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $model->name ?></h4>
</div>

<div class="modal-body">
  <?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>
  <?= $this->render('_user_info', ['model' => $model, 'currency' => $currency]); ?>
  <?= $form->field($model, 'reject_reason')->textarea(); ?>
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


