<?php
use admin\modules\alerts\models\Event;
use yii\helpers\Url;

/** @var \admin\modules\alerts\models\Event $model */
/** @var mcms\common\form\ActiveKartikForm $form */

/** @var integer $checkingTypeLimit */
$checkingTypeLimit = Event::CHECKING_LIMIT;
// При переключении на "Достижение порога" дисейблим ненужные поля
$js = <<<JS
function checkType() {
  var more = $('#event-more');
  var less = $('#event-less');
  var is_percent = $('#event-is_percent');
  var interval_periods_sample = $('#event-interval_periods_sample');
  var is_consider_last_days = $('#event-is_consider_last_days');
  var checkingType = $("input[name='Event[checking_type]']:checked").val();
  
  less.attr('disabled', false);
  is_percent.attr('disabled', false);
  interval_periods_sample.attr('disabled', false);
  is_consider_last_days.attr('disabled', false);
  
  
  if (checkingType == $checkingTypeLimit) {
    more.prop('checked', true );
    less.prop('checked', false );
    less.attr('disabled', true);
    
    is_percent.prop('checked', false );
    is_percent.attr('disabled', true);
    
    interval_periods_sample.val('');
    interval_periods_sample.attr('disabled', true);
    
    is_consider_last_days.prop('checked', false );
    is_consider_last_days.attr('disabled', true);
  }
}
checkType();
$(document).on('change', '#event-checking_type input[type=radio]', function() {
  checkType();
});
JS;

$this->registerJs($js);


?>
  <div class="row">
    <div class="col-md-6">
      <?= $model->getAttributeLabel('checking_type') ?>
    </div>
    <div class="col-md-6">
      <?= $form->field($model, 'checking_type')->radioList(Event::getCheckingTypes(), [
        'inline' => true
      ])->label(false) ?>
    </div>
  </div>
<div class="row">
  <div class="col-md-6">
    <?= $model->getAttributeLabel('name') ?>
  </div>
  <div class="col-md-6">
    <?= $form->field($model, 'name')->label(false) ?>
  </div>
</div>
<div class="row">
  <div class="col-md-6">
    <?= $model->getAttributeLabel('priority') ?>
  </div>
  <div class="col-md-6">
    <?= $form->field($model, 'priority')->radioList(Event::getPriorities(), [
      'inline' => true
    ])->label(false) ?>
  </div>
</div>
<div class="row">
  <div class="col-md-6">
    <?= $model->getAttributeLabel('metric') ?>?
  </div>
  <div class="col-md-6">
    <?= $form->field($model, 'metric')->dropDownList(Event::getMetrics(), ['prompt' => Yii::_t('app.common.choose')])->label(false) ?>
  </div>
</div>
<div class="row">
  <div class="col-md-6">
    <?= Yii::_t('alerts.event.what_happened') ?>?
  </div>
  <div class="col-md-6">
    <?= $form->field($model, 'more')->checkbox(['container' => false]) ?>
    <?= $form->field($model, 'less')->checkbox(['container' => false]) ?>
  </div>
</div>
<div class="row">
  <div class="col-md-6">
    <?= $model->getAttributeLabel('value') ?>?
  </div>
  <div class="col-md-4">
    <?= $form->field($model, 'value')->label(false) ?>
  </div>
  <div class="col-md-2">
    <?= $form->field($model, 'is_percent')->checkbox(['container' => false]) ?>
  </div>
</div>
<div class="row">
  <div class="col-md-6">
    <?= $model->getAttributeLabel('minutes') ?>?
  </div>
  <div class="col-md-6">
    <?= $form->field($model, 'minutes')->label(false)->hint(Yii::_t('alerts.event.hint-minutes')) ?>
  </div>
</div>
<div class="row">
  <div class="col-md-6">
    <?= $model->getAttributeLabel('interval_periods_sample') ?>
  </div>
  <div class="col-md-6">
    <?= $form->field($model, 'interval_periods_sample')->label(false)->hint(Yii::_t('alerts.event.hint-interval_periods_sample')) ?>
  </div>
</div>
<div class="row">
  <div class="col-md-6">
    <?= $model->getAttributeLabel('is_consider_last_days') ?>
  </div>
  <div class="col-md-6">
      <?= $form->field($model, 'is_consider_last_days')->checkbox(['container' => false, 'label' => false])->hint(Yii::_t('alerts.event.hint-is_consider_last_days')) ?>
  </div>
</div>
  <div class="row">
    <div class="col-md-6">
      <?= $model->getAttributeLabel('check_interval') ?>
    </div>
    <div class="col-md-6">
      <?= $form->field($model, 'check_interval')->textInput()->label(false) ?>
    </div>
  </div>


<?= $form->field($model, 'emails')->textarea()->hint(Yii::_t('alerts.event.hint-emails', ['link' => Url::to(['/notifications/settings/view/', 'id' => 'alerts'])])); ?>
