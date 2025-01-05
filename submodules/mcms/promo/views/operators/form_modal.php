<?php
use mcms\common\form\AjaxActiveForm;
use mcms\common\helpers\Html;
use mcms\common\widget\modal\Modal;
/** @var $model \mcms\promo\models\Operator */
/** @var $canChangeOperatorShowServiceUrl bool */

?>
<?php $form = AjaxActiveForm::begin([
  'ajaxSuccess' => Modal::ajaxSuccess('#operatorsPjaxGrid'),
]); ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h4 class="modal-title"><?=$this->title?></h4>
  </div>
  <div class="modal-body">
    <div class="row">
      <div class="col-sm-8">
        <?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>

        <?= $form->field($model, 'country_id')->dropDownList($countries, ['prompt' => Yii::_t('app.common.not_selected')]); ?>

        <?= $form->field($model, 'name'); ?>

        <?= $form->field($model, 'status')->dropDownList($model->getStatuses()); ?>
        <?= $form->field($model, 'is_3g')->checkbox(); ?>
        <?= $form->field($model, 'is_geo_default')->checkbox(); ?>
        <?php if ($canChangeOperatorShowServiceUrl):?>
          <?= $form->field($model, 'show_service_url')->checkbox(); ?>
        <?php endif;?>
        <?= $form->field($model, 'is_trial')->checkbox(); ?>

      </div>
    </div>
    <div class="row">
      <div class="col-sm-12">
        <?= $form->field($model, 'ipTextarea')->textArea(['rows' => 10])->hint(Yii::_t('promo.operators.attribute_help-ipTextarea')); ?>
      </div>
    </div>
  </div>
  <div class="modal-footer">
    <?= Html::submitButton(Yii::_t('app.common.Save'), ['class' => $model->isNewRecord ? 'btn btn-success pull-right' : 'btn btn-primary pull-right']) ?>
  </div>
<?php $form->end() ?>