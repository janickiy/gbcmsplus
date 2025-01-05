<?php

use kartik\builder\Form;
use kartik\widgets\DepDrop;
use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\multilang\widgets\multilangform\MultiLangForm;
use mcms\common\widget\modal\Modal;
use mcms\pages\models\FaqCategory;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model mcms\pages\models\Faq */
/* @var $form yii\widgets\ActiveForm */
/* @var $canViewDropDown bool */
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
        'question' => ['type' => Form::INPUT_TEXT],
        'answer' => ['type' => Form::INPUT_TEXTAREA, 'options' => ['rows' => 6]],
      ]
    ]); ?>

    <h3><?php echo Yii::_t('faq.settings') ?></h3>
    <?= $form->field($model, 'faq_category_id')->dropDownList(FaqCategory::getAllCategoriesDropDownArray(), [
      'id' => 'faq_category_id_select'
    ]); ?>
    <?= Html::hiddenInput('faq_id', $model->isNewRecord ? "" : $model->id, ['id' => 'faq_id']); ?>
    <?= $canViewDropDown
      ? $form->field($model, 'sort')->widget(DepDrop::class, [
      'data' => $model->faq_category_id ? [$model->faq_category_id => $model->faqCategory->name] : [],
      'pluginOptions' => [
        'initialize' => true,
        'placeholder' => Yii::_t('faq.choose_sort'),
        'depends' => ['faq_category_id_select'],
        'url' => Url::to(['faq/get-sort-drop-down-array/']),
        'params' => ['faq_id']
      ],
      'options' => [
        'class' => 'form-control input-sm'
      ]
    ])
    : $form->field($model, 'sort');
    ?>
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