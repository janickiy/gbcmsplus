<?php

use kartik\form\ActiveForm;
use kartik\builder\Form;
use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\multilang\widgets\multilangform\MultiLangForm;
use mcms\common\widget\modal\Modal;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model mcms\pages\models\FaqCategory */
/* @var $form yii\widgets\ActiveForm */
?>

<?php
$form = AjaxActiveKartikForm::begin([
  'ajaxSuccess' => Modal::ajaxSuccess('#pages-pjax')
]); ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>
  <div class="modal-body">
    <?= MultiLangForm::widget([
      'model' => $model,
      'form' => $form,
      'attributes' => [
        'name' => ['type' => Form::INPUT_TEXT]
      ]
    ]); ?>


    <h3><?php echo Yii::_t('faq.settings') ?></h3>
    <?= $form->field($model, 'sort')->dropDownList($model->getDropDownCategoryRangeArray()); ?>
    <?= $form->field($model, 'visible')->checkbox(); ?>
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