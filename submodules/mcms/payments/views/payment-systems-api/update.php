<?php
use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\grid\ContentViewPanel;
//use mcms\common\helpers\Html;
use yii\bootstrap\Html;
use mcms\common\widget\modal\Modal;
use yii\web\View;
use yii\widgets\Pjax;
use mcms\payments\components\widgets\FormBuilder;

/** @var View $this */
/** @var \mcms\payments\models\paysystems\api\BaseApiSettings[] $models */
/** @var \mcms\payments\models\paysystems\PaySystemApiGroup $group */
/** @var bool $checkAllCurrency Чекнуть ли галочку "Применить на все валюты" */

?>

<?php ContentViewPanel::begin([
  'padding' => false,
  'header' => $group->code,
]);
?>
<div class="panel-body">

  <?php Pjax::begin([
    'id' => 'form-pjax',
    'enablePushState' => false,
    'enableReplaceState' => false,
  ]) ?>

  <?php
  $confirmText = Yii::_t('payments.payment-systems-api.settings-apply-to-group_confirm');
  // TODO Рефакторинг: аналогичный код по валидации в табах есть в mcms/modmanager/views/modules/settings.php. Нужно вынести в общий компонент
  $js = <<< JS
var form = $('#form-build-form');
var checkboxes = $('input[name="settings-to-apply-on-group"]');
form.on('afterValidate', function () {
  var error = $('div.has-error').first();
  if (error.length === 0) return;

  if (checkboxes.filter(':checked').val()) return;
  
  var tabName = error.closest('.tab-pane').attr('id');
  $('.nav-tabs a[href="#' + tabName + '"]').tab('show')
});

checkboxes.on('change', function () {
  var checkbox = $(this);
  var currentValue = checkbox.val();
  var checked = checkbox.is(':checked');
  
  form.find('.nav-tabs > li:not([data-currency="'+currentValue+'"])').each(function (i, tab) {
    if (checked) {
      $(tab).addClass('disabled').find('a').attr('data-toggle', 'false').bind('click.disableTab', function(){return false;});
    } else {
      $(tab).removeClass('disabled').find('a').attr('data-toggle', 'tab').unbind('click.disableTab');
    }
  });
});

form.find('[type=submit]').on('click', function () {
  if (!checkboxes.filter(':checked').val()) return true;
  
  yii.confirm('$confirmText', function () {
    form.trigger('submit');
  });
  return false;
});

JS;
  $this->registerJs($js, View::POS_END);

  ?>

  <?php $form = AjaxActiveKartikForm::begin([
    'id' => 'form-build-form',
    'ajaxSuccess' => Modal::ajaxSuccess('#form-pjax'),
    'options' => ['enctype' => 'multipart/form-data'],
  ]) ?>

    <ul class="nav nav-tabs nav-settings bordered">
      <?php $firstModel = true; ?>
      <?php foreach ($models as $currency => $model) { ?>
          <li<?= $firstModel ? ' class="active"' : null ?> data-currency="<?=$currency?>">
              <a href="#settings_tab_<?= $currency ?>" data-toggle="tab">
                <?= Yii::$app->formatter->asStringOrNull(strtoupper($currency)) ?>
              </a>
          </li>
        <?php $firstModel = false; ?>
      <?php } ?>
    </ul>

    <div class="tab-content padding-10">
      <?php $firstModel = true; ?>
      <?php foreach ($models as $currency => $model) { ?>
          <div class="tab-pane<?= $firstModel ? ' active' : null ?>" id="settings_tab_<?= $currency ?>">
            <?= FormBuilder::widget([
              'model' => $model,
              'form' => $form,
            ]) ?>
            <?= $model->getAccessTokenUrl()
              ? Html::a(Yii::_t('payments.payment-systems-api.get-access-token'), $model->getAccessTokenUrl(), ['data-pjax' => 0])
              : '' ?>
            <?php if (count($models) > 1) { ?>
              <?= Html::checkbox(
                'settings-to-apply-on-group',
                null,
                [
                  'label' => Yii::_t('payments.payment-systems-api.settings-apply-to-group'),
                  'value' => $model->getPaysystemApi()->currency,
                ]
              ) ?>
              <p class="note"><?= Yii::_t('payments.payment-systems-api.settings-apply-to-group_hint') ?></p>
            <?php } ?>
          </div>
        <?php if ($checkAllCurrency && $firstModel) {?>
          <?php $this->registerJs("checkboxes.filter('[value=$currency]').prop('checked', true).trigger('change')", View::POS_END); ?>
        <?php } ?>
        <?php $firstModel = false; ?>
      <?php } ?>
    </div>

    <br/>

    <div class="row">
        <div class="col-md-12">
          <?= Html::submitButton(Yii::_t('app.common.Save'), ['class' => 'btn btn-success pull-right']) ?>
        </div>
    </div>

  <?php AjaxActiveKartikForm::end() ?>
  <?php Pjax::end() ?>

</div>

<?php ContentViewPanel::end() ?>
