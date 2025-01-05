<?php
use kartik\form\ActiveForm;
use kartik\helpers\Html;
use yii\helpers\Url;
use rgk\utils\widgets\DateRangePicker;
use kartik\widgets\DatePicker;

/* @var $this yii\web\View */
/* @var $model mcms\statistic\models\search\PostbackDataSearch */
?>


  <?php $form = ActiveForm::begin([
    'method' => 'get',
    'action' => ['/' . Yii::$app->controller->getRoute()],
    'type' => ActiveForm::TYPE_INLINE,
    'options' => [
      'data-pjax' => true,
      'id' => 'filter-form',
    ],
  ]); ?>
    <div class="well">
      <div class="row">

        <div class="col-sm-3 col-xs-3 margin-bottom-10">
          <?= DateRangePicker::widget([
            'model' => $model,
            'attribute' => 'dateRange',
          ]); ?>
        </div>

        <div class="col-sm-3 col-xs-3 margin-bottom-10">
          <?= $form->field($model, 'handler_code')->dropDownList($model->getHandlers(), ['prompt'=>'Handler']) ?>
        </div>

        <div class="col-sm-3 col-xs-3 margin-bottom-10">
          <?= $form->field($model, 'transType')->dropDownList($model->getTransTypes(), ['prompt'=>'Trans type']) ?>
        </div>

        <div class="col-sm-3 col-xs-3 margin-bottom-10">
          <?= DatePicker::widget([
            'model' => $model,
            'attribute' => 'actionDate',
            'pluginOptions' => [
              'format' => 'yyyy-mm-dd',
              'autoclose' => true,
              'orientation' => 'bottom'
            ],
            'options' => ['placeholder' => 'Action date']
          ]); ?>
        </div>

        <div class="col-sm-3 col-xs-3 margin-bottom-10">
          <?= $form->field($model, 'hitId') ?>
        </div>

        <div class="col-sm-3 col-xs-3 margin-bottom-10">
          <?= $form->field($model, 'transId') ?>
        </div>

        <div class="col-sm-3 col-xs-3 margin-bottom-10">
          <?= $form->field($model, 'partnerId') ?>
        </div>

        <div class="col-sm-3 col-xs-3 margin-bottom-10">
          <?= $form->field($model, 'sourceId') ?>
        </div>

        <div class="col-sm-3 col-xs-3 margin-bottom-10">
          <?= $form->field($model, 'phone') ?>
        </div>

        <div class="col-sm-3 col-xs-3 margin-bottom-10">
          <?= $form->field($model, 'currency') ?>
        </div>

        <div class="col-sm-3 col-xs-3 margin-bottom-10">
          <?= $form->field($model, 'data') ?>
        </div>

        <div class="col-sm-3 pull-right">
          <?= Html::submitButton(Yii::_t('statistic.filter_submit'), ['class' => 'btn btn-info pull-right'])?>
          <?= Html::a(
            Yii::_t('statistic.filter_reset'),
            Url::to(['/' . Yii::$app->controller->getRoute()]),
            ['class' => 'btn btn-default pull-right', 'style' => 'margin-right: 10px']
          ) ?>
        </div>

      </div>

    </div>


  <?php ActiveForm::end(); ?>

