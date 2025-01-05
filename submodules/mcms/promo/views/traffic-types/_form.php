<?php
use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\multilang\widgets\multilangform\MultiLangForm;
use mcms\common\helpers\Html;
use mcms\common\widget\modal\Modal;

?>


<?php $form = AjaxActiveKartikForm::begin([
  'action' => $model->isNewRecord ? ['/promo/traffic-types/create'] : ['/promo/traffic-types/update', 'id' => $model->id],
  'ajaxSuccess' => Modal::ajaxSuccess('#trafficTypesGrid'),
  'forceResultMessages' => true,
]); ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>

  <div class="modal-body">
    <div class="row">
      <div class="col-sm-6"><?= MultiLangForm::widget([
          'model' => $model,
          'form' => $form,
          'attributes' => [
            'name' => ['type' => \kartik\builder\Form::INPUT_TEXT]
          ]
        ]); ?></div>
      <div class="col-sm-6">
        <div class="well">
          <?= $form->field($model, 'status')->dropDownList($model->getStatuses()) ?>
        </div>
      </div>
    </div>
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