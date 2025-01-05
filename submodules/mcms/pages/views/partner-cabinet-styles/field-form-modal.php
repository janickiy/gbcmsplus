<?php
use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\helpers\ArrayHelper;
use mcms\common\multilang\widgets\input\InputWidget;
use mcms\common\widget\modal\Modal;
use mcms\pages\models\PartnerCabinetStyleCategory;
use mcms\pages\models\PartnerCabinetStyleField;
use yii\bootstrap\Html;

/** @var PartnerCabinetStyleField $model */
/** @var PartnerCabinetStyleCategory[] $categories */
$emptyStyle = '___';
?>

<?php $form = AjaxActiveKartikForm::begin([
  'ajaxSuccess' => Modal::ajaxSuccess('#style-categories-pjax'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $model->isNewRecord ? $model->translate('create') : $model->name;?></h4>
</div>

<div class="modal-body">
  <div class="row">
    <div class="col-md-4" style="border-right: 2px solid #e5e5e5">
      <?= $form->field($model, 'category_id')->dropDownList(
        ArrayHelper::map($categories, 'id', 'name'),
        ['prompt' => Yii::_t('app.common.not_selected')]
      ); ?>
      <?= $form->field($model, 'name')->widget(InputWidget::class, [
          'class' => 'form-control',
          'form' => $form,
        ]); ?>
      <?= $form->field($model, 'code'); ?>
      <div class="row">
        <div class="col-md-6">
          <?= $form->field($model, 'sort'); ?>
        </div>
        <div class="col-md-6">
          <?= $form->field($model, 'sort_css'); ?>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="row">
        <div class="col-md-4">
          <?= $form->field($model, 'css_selector'); ?>
        </div>
        <div class="col-md-4">
          <?= $form->field($model, 'css_prop'); ?>
        </div>
        <div class="col-md-4">
          <?= $form->field($model, 'default_value'); ?>
        </div>
      </div>
      <p><?= $model::translate('result_preview')?>:</p>
      <pre>
<span id="pcabinet_css_selector"><?= $model->css_selector ?: $emptyStyle ?></span> {
  <span id="pcabinet_css_prop"><?= $model->css_prop ?: $emptyStyle ?></span>: <span id="pcabinet_default_value"><?= $model->default_value ?: $emptyStyle ?></span>;
}
</pre>
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



<?php
$idPrefix = 'partnercabinetstylefield-';
$script = <<< JS
// Предпросмотр стилей
$(document).on(
  'keyup blur',
  '#{$idPrefix}css_selector, #{$idPrefix}css_prop, #{$idPrefix}default_value', 
  function(e){
  var selector = $('#{$idPrefix}css_selector').val() !== '' ? $('#{$idPrefix}css_selector').val() : '$emptyStyle';
  var prop = $('#{$idPrefix}css_prop').val() !== '' ? $('#{$idPrefix}css_prop').val() : '$emptyStyle';
  var val = $('#{$idPrefix}default_value').val() !== '' ? $('#{$idPrefix}default_value').val() : '$emptyStyle';
  
  $('#pcabinet_css_selector').html(selector);
  $('#pcabinet_css_prop').html(prop);
  $('#pcabinet_default_value').html(val);
});
JS;

$this->registerJs($script, yii\web\View::POS_READY);