<?php
use yii\widgets\ActiveForm;
use mcms\common\helpers\Html;

?>
<?php $this->beginBlock('actions'); ?>
<?= $this->render('actions/update', ['model' => $model]); ?>
<?php $this->endBlock() ?>

<?php $form = ActiveForm::begin(); ?>
<?= $form->field($model, 'redirect_to')->dropDownList($model->redirectToDropDownList, ['prompt' => Yii::_t('app.common.not_selected')]); ?>

  <div class="form-group">
    <?= Html::submitButton(Yii::_t('app.common.Save'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
  </div>

<?php ActiveForm::end(); ?>