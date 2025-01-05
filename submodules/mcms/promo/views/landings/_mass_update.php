<?php
use mcms\common\form\AjaxActiveKartikForm;
use mcms\promo\models\LandingMassModel;
use mcms\promo\models\LandingOperator;
use mcms\promo\models\Provider;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\bootstrap\Html as BHtml;

/**
 * @var LandingMassModel $model
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
  'fieldClass' => \yii\widgets\ActiveField::class,
//  'type' => AjaxActiveKartikForm::TYPE_HORIZONTAL,
  'options' => ['class' => 'text-left'],
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

  <div class="row">
    <div class="col-xs-10">
      <?= $form->field($model, 'offer_category_id')->dropDownList($model->model->offerCategories, [
        'class' => 'form-control edit-value pseudo-disabled',
        'prompt' => Yii::_t('app.common.not_selected')
      ]) ?>

    </div>
    <div class="col-xs-2">
      <?= $form
        ->field($model, 'edit[]', ['template' => '{input}', 'options' => ['tag' => 'span']])
        ->checkbox(['class' => 'control-checkbox enable-edit', 'value' => 'offer_category_id', 'data-attribute' => Html::getInputName($model, 'offer_category_id')], false)
      ?>
    </div>
  </div>
  <div class="row">
    <div class="col-xs-10">
      <?= $form->field($model, 'category_id')->dropDownList($model->model->categories, [
        'class' => 'form-control edit-value pseudo-disabled',
        'prompt' => Yii::_t('app.common.not_selected')
      ]) ?>

    </div>
    <div class="col-xs-2">
      <?= $form
        ->field($model, 'edit[]', ['template' => '{input}', 'options' => ['tag' => 'span']])
        ->checkbox(['class' => 'control-checkbox enable-edit', 'value' => 'category_id', 'data-attribute' => Html::getInputName($model, 'category_id')], false)
      ?>
    </div>
  </div>
  <div class="row">
    <div class="col-xs-10">
        <?= $form->field($model, 'access_type')->dropDownList($model->model->accessTypes, [
          'class' => 'form-control edit-value pseudo-disabled',
          'prompt' => Yii::_t('app.common.not_selected')
        ]) ?>

    </div>
    <div class="col-xs-2">
      <?= $form
        ->field($model, 'edit[]', ['template' => '{input}', 'options' => ['tag' => 'span']])
        ->checkbox(['class' => 'control-checkbox enable-edit', 'value' => 'access_type', 'data-attribute' => Html::getInputName($model, 'access_type')], false)
      ?>
    </div>
  </div>
  <div class="row">
    <div class="col-xs-10">
        <?= $form->field($model, 'status')->dropDownList($model->model->statuses, [
          'class' => 'form-control edit-value pseudo-disabled',
          'prompt' => Yii::_t('app.common.not_selected')
        ]) ?>

    </div>
    <div class="col-xs-2">
      <?= $form
        ->field($model, 'edit[]', ['template' => '{input}', 'options' => ['tag' => 'span']])
        ->checkbox(['class' => 'control-checkbox enable-edit', 'value' => 'status', 'data-attribute' => Html::getInputName($model, 'status')], false)
      ?>
    </div>
  </div>
  <div class="row">
    <div class="col-xs-10">
        <?= $form->field($model, 'local_currency_id')->dropDownList((new LandingOperator())->currencies, [
          'class' => 'form-control edit-value pseudo-disabled',
        ]) ?>

    </div>
    <div class="col-xs-2">
      <?= $form
        ->field($model, 'edit[]', ['template' => '{input}', 'options' => ['tag' => 'span']])
        ->checkbox(['class' => 'control-checkbox enable-edit', 'value' => 'local_currency_id', 'data-attribute' => Html::getInputName($model, 'local_currency_id')], false)
      ?>
    </div>
  </div>
  <div class="row">
    <div class="col-xs-10">
        <?= $form->field($model, 'buyout_price_rub')->textInput([
          'class' => 'form-control edit-value pseudo-disabled',
        ]) ?>

    </div>
    <div class="col-xs-2">
      <?= $form
        ->field($model, 'edit[]', ['template' => '{input}', 'options' => ['tag' => 'span']])
        ->checkbox(['class' => 'control-checkbox enable-edit', 'value' => 'buyout_price_rub', 'data-attribute' => Html::getInputName($model, 'buyout_price_rub')], false)
      ?>
    </div>
  </div>
  <div class="row">
    <div class="col-xs-10">
        <?= $form->field($model, 'buyout_price_eur')->textInput([
          'class' => 'form-control edit-value pseudo-disabled',
        ]) ?>

    </div>
    <div class="col-xs-2">
      <?= $form
        ->field($model, 'edit[]', ['template' => '{input}', 'options' => ['tag' => 'span']])
        ->checkbox(['class' => 'control-checkbox enable-edit', 'value' => 'buyout_price_eur', 'data-attribute' => Html::getInputName($model, 'buyout_price_eur')], false)
      ?>
    </div>
  </div>
  <div class="row">
    <div class="col-xs-10">
        <?= $form->field($model, 'buyout_price_usd')->textInput([
          'class' => 'form-control edit-value pseudo-disabled',
        ]) ?>

    </div>
    <div class="col-xs-2">
      <?= $form
        ->field($model, 'edit[]', ['template' => '{input}', 'options' => ['tag' => 'span']])
        ->checkbox(['class' => 'control-checkbox enable-edit', 'value' => 'buyout_price_usd', 'data-attribute' => Html::getInputName($model, 'buyout_price_usd')], false)
      ?>
    </div>
  </div>
  <div class="row">
    <div class="col-xs-10">
        <?= $form->field($model, 'local_currency_rebill_price')->textInput([
          'class' => 'form-control edit-value pseudo-disabled',
        ]) ?>

    </div>
    <div class="col-xs-2">
      <?= $form
        ->field($model, 'edit[]', ['template' => '{input}', 'options' => ['tag' => 'span']])
        ->checkbox(['class' => 'control-checkbox enable-edit', 'value' => 'local_currency_rebill_price', 'data-attribute' => Html::getInputName($model, 'local_currency_rebill_price')], false)
      ?>
    </div>
  </div>
  <div class="row">
    <div class="col-xs-10">
        <?= $form->field($model, 'rebill_price_rub')->textInput([
          'class' => 'form-control edit-value pseudo-disabled',
        ]) ?>

    </div>
    <div class="col-xs-2">
      <?= $form
        ->field($model, 'edit[]', ['template' => '{input}', 'options' => ['tag' => 'span']])
        ->checkbox(['class' => 'control-checkbox enable-edit', 'value' => 'rebill_price_rub', 'data-attribute' => Html::getInputName($model, 'rebill_price_rub')], false)
      ?>
    </div>
  </div>

  <div class="row">
    <div class="col-xs-8"></div>
    <div class="col-xs-4 text-right">
      <?= Html::submitButton(Yii::_t('app.common.Save'), ['class' => 'btn btn-primary submit-button', 'disabled' => true]); ?>
    </div>
  </div>
<?php AjaxActiveKartikForm::end(); ?>
<?php Modal::end(); ?>