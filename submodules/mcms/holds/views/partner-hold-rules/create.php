<?php
/** @var \yii\web\View $this */
/** @var \mcms\holds\models\HoldProgram $model */
use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\helpers\Html;

?>
<?php $form = AjaxActiveKartikForm::begin() ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>

  <div class="modal-body">
    <?= $this->render('_params-form-content', ['model' => $model, 'form' => $form]) ?>
  </div>

  <div class="modal-footer">
    <div class="row">
      <div class="col-md-12">
        <?= Html::submitButton(
          '<i class="fa fa-save"></i> ' . Yii::_t('app.common.Create'), ['class' => 'pull-right btn btn-success']
        ) ?>
      </div>
    </div>
  </div>
<?php AjaxActiveKartikForm::end() ?>