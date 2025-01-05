<?php

use kartik\widgets\DatePicker;
use rgk\utils\widgets\form\AjaxActiveForm;


/** @var \yii\web\View $this */

$js = <<<JS
function startGeneration(res) {
  document.getElementById('response').innerText = res.join("\\n");
}
JS;

$this->registerJs($js);
?>

<div class="panel panel-default">
  <div class="panel-body">
    <?php $form = AjaxActiveForm::begin([
      'action' => ['/statistic/traffic-generator/index'],
      'ajaxSuccess' => 'startGeneration',
    ]); ?>
    <div class="row">
      <div class="col-md-6">
        <?= $form->field($model, 'pbHandlerUrl') ?>
      </div>
      <div class="col-md-6">
        <?= $form->field($model, 'hitHandlerUrl') ?>
      </div>
    </div>
    <div class="row">
      <div class="col-md-2">
        <?= $form->field($model, 'kpSecret') ?>
        <?= $form->field($model, 'inaccuracyPercent') ?>
      </div>
      <div class="col-md-2">
        <?= $form->field($model, 'subsPercent') ?>
        <?= $form->field($model, 'complainsPercent') ?>
      </div>
      <div class="col-md-2">
        <?= $form->field($model, 'rebillsPercent') ?>
        <?= $form->field($model, 'offsPercent') ?>
      </div>
      <div class="col-md-2">
        <?= $form->field($model, 'hitsCount') ?>
        <?= $form->field($model, 'hitsDateFrom')->widget(DatePicker::class, [
          'pickerButton' => false,
          'type' => DatePicker::TYPE_COMPONENT_APPEND,
          'pluginOptions' => [
            'endDate' => Yii::$app->formatter->asDate('today', 'php:Y-m-d'),
            'format' => 'yyyy-mm-dd',
            'autoclose' => true,
            'orientation' => 'bottom',
            'weekStart' => 1
          ],
          'options' => [
            'autocomplete' => 'off',
          ]
        ]) ?>
      </div>
      <div class="col-md-2">
        <?= $form->field($model, 'sourceId') ?>
      </div>
      <div class="col-md-2">
        <?= $form->field($model, 'operatorId') ?>
        <div class="form-group clearfix">
          <label class="control-label">&#160;</label>
          <input type="submit" value="GENERATE" class="btn btn-primary form-control"/>
        </div>
      </div>
    </div>
    <?php AjaxActiveForm::end(); ?>


  </div>
</div>

<pre id="response">
</pre>