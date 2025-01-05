<?php
use mcms\common\grid\ContentViewPanel;
use yii\widgets\ActiveForm;
use mcms\common\helpers\Html;
/** @var $model \mcms\promo\models\Operator */

?>

<?php ContentViewPanel::begin([]) ?>
<div class="row">
  <div class="col-sm-8">
    <?php $form = ActiveForm::begin(['id' => 'operators-form']); ?>

    <?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>

    <?= $form->field($model, 'country_id')->dropDownList($countries, ['prompt' => Yii::_t('app.common.not_selected')]); ?>

    <?= $form->field($model, 'name'); ?>

    <?= $form->field($model, 'status')->dropDownList($model->getStatuses()); ?>
    <?= $form->field($model, 'is_3g')->checkbox(); ?>
    <?= $form->field($model, 'is_geo_default')->checkbox(); ?>
    <?= $form->field($model, 'is_trial')->checkbox(); ?>

    <?= $form->field($model, 'ipTextarea')->textArea(['rows' => 10])->hint(Yii::_t('promo.operators.attribute_help-ipTextarea')); ?>

  </div>
</div>







  <hr>
  <div class="form-group clearfix">
    <?= Html::submitButton(Yii::_t('app.common.Save'), ['class' => $model->isNewRecord ? 'btn btn-success pull-right' : 'btn btn-primary pull-right']) ?>
  </div>

<?php ActiveForm::end(); ?>

<?php ContentViewPanel::end() ?>