<?php
use mcms\common\form\AjaxActiveKartikForm;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\bootstrap\Html as BHtml;

/**
 * @var \yii\base\Model $model
 * @var \yii\web\View $this
 */

$js = <<<JS
$(document).on('change', '[name="selection[]"], .select-on-check-all', function(event) {
  var selection = [];
  $('[name="selection[]"]').each(function(ind, element) {
    var element = $(element);
    if (element.is(":checked")) {
      selection.push(element.val());
    }
  });
  $('.mass-button').prop('disabled', !selection.length);
});

$('.enable-edit').on('change', function() {
  var element = $(this);
  
  $('[name="' + element.data('attribute') + '"]').toggleClass('pseudo-disabled', !element.is(':checked'));
  
  var somethingIsChecked = false;
  $('.enable-edit').each(function(ind, element) {
     somethingIsChecked = somethingIsChecked || $(element).is(':checked');
  });
  $('.submit-button').prop('disabled', !somethingIsChecked);
});

$('.edit-value').on('keydown', function() {
  var element = $(this);
  var name = element.attr('name');

  element.removeClass('pseudo-disabled');

  $('[data-attribute="' + name + '"]').prop('checked', true);
  $('.submit-button').prop('disabled', false);
});

$('{$pjaxId}').on('pjax:complete', function() {
  $('.mass-button').prop('disabled', true);
});
JS;

$this->registerJs($js);

$onShow = <<<JS
function () {
  var selection = [];
  $('[name="selection[]"]').each(function(ind, element) {
    var element = $(element);
    if (element.is(":checked")) {
      selection.push(element.val());
    }
  });
  $('[name="selection"]').val(selection.join(','));
}
JS;

?>

<?php Modal::begin([
    'header' => '<h2>' . Yii::_t('commonMsg.main.mass-update-label') . '</h2>',
    'toggleButton' => ['label' => BHtml::icon('edit') . ' ' . Yii::_t('commonMsg.main.mass-update-label'), 'class' => 'btn btn-xs btn-success mass-button', 'disabled' => true],
    'options' => ['class' => 'custom-modal'],
    'clientEvents' => [
      'shown.bs.modal' => $onShow
    ]
  ]);
?>
<div class="alert alert-warning" role="alert"><?= Yii::_t('commonMsg.main.mass-update-info') ?></div>
<?php $form = AjaxActiveKartikForm::begin([
  'action' => ['mass-update'],
  'type' => AjaxActiveKartikForm::TYPE_HORIZONTAL,
  'formConfig' => [
     'labelSpan' => 4,
     'deviceSize' => AjaxActiveKartikForm::SIZE_MEDIUM,
     'showLabels' => true,
     'showErrors' => true,
     'showHints' => false
  ],
  'ajaxSuccess' => "function(response) { if (response && response.success) { $.pjax.reload({container : '$pjaxId', 'timeout' : 5000}); $('.custom-modal').modal('hide') } }"
//  'ajaxSuccess' => "function() { $.pjax.reload({container : '$pjaxId', 'timeout' : 5000}); $('.custom-modal').modal('hide') }"
]) ?>
  <?= Html::hiddenInput('selection', ''); ?>

  <?php foreach($model->fields() as $column): ?>
    <div class="form-group row">
      <div class="col-xs-10">
        <?= $form->field($model, $column)->textInput(['class' => 'form-control edit-value pseudo-disabled']) ?>
      </div>
      <div class="col-xs-2">
        <?= $form
            ->field($model, 'edit[]', ['template' => '{input}', 'options' => ['tag' => 'span']])
            ->checkbox(['class' => 'control-checkbox enable-edit', 'value' => $column, 'data-attribute' => Html::getInputName($model, $column)], false)
        ?>
      </div>
    </div>
  <?php endforeach; ?>
  <div class="row">
    <div class="col-xs-8"></div>
    <div class="col-xs-4 text-right">
      <?= Html::submitButton(Yii::_t('app.common.Save'), ['class' => 'btn btn-primary submit-button', 'disabled' => true]); ?>
    </div>
  </div>
<?php AjaxActiveKartikForm::end(); ?>
<?php Modal::end(); ?>