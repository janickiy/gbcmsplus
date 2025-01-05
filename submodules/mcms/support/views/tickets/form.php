<?php

use mcms\common\form\AjaxActiveForm;
use mcms\common\widget\modal\Modal;
use yii\helpers\Html;
use mcms\support\models\Support;
use mcms\common\helpers\ArrayHelper;
use mcms\support\models\SupportCategory;
use yii\widgets\ActiveForm;
use mcms\common\widget\UserSelect2;

/**
 * @var yii\web\View $this
 * @var Support $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<?php $form = AjaxActiveForm::begin([
  'ajaxSuccess' => Modal::ajaxSuccess('#tickets-list-grid'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $this->title ?></h4>
</div>

<div class="modal-body">
<?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>
<?= $form->field($model, 'name') ?>
<?= $form->field($model, 'question')->textarea() ?>
<?= $form->field($model, 'created_by')->widget(UserSelect2::class, [
  'attribute' => 'created_by',
  'roles' => ['partner'],
  'options' => [
    'placeholder' => Yii::_t('users.forms.enter_login_or_email') . ':'
  ]
]) ?>
<?= $form->field($model, 'support_category_id')->dropDownList(ArrayHelper::map(SupportCategory::findEnabled(), 'id', 'name'), ['prompt' => '']) ?>
</div>

<div class="modal-footer">
  <div class="row">
    <div class="col-md-12">
      <?= Html::submitButton(
        '<i class="fa fa-save"></i> ' . Yii::_t('app.common.Create'),
        ['class' => 'btn btn-success']
      ) ?>
    </div>
  </div>
</div>
<?php AjaxActiveForm::end(); ?>
