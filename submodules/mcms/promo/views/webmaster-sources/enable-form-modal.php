<?php

use mcms\common\widget\modal\Modal;
use yii\helpers\Html;
use mcms\promo\models\Source;
use mcms\common\form\AjaxActiveForm;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var Source $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<?php $form = AjaxActiveForm::begin([
  'action' => ['webmaster-sources/enable-modal', 'id' => $model->id],
  'ajaxSuccess' => Modal::ajaxSuccess('#webmaster-sources-pjax'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $model->name ?></h4>
</div>

<div class="modal-body">
  <?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>
  <?= $form->field($model, 'category_id')->dropDownList($model->categories, ['prompt' => Yii::_t('app.common.choose')]) ?>
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


