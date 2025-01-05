<?php
use mcms\statistic\components\newStat\FormModel;
use kartik\form\ActiveForm;
use mcms\statistic\Module;
use rgk\utils\widgets\DateRangePicker;
use yii\web\JsExpression;

/** @var FormModel $formModel */
/** @var int $maxGroups */
/** @var ActiveForm $form */

/** @var Module $module */
$module = Yii::$app->getModule('statistic');
$pluginOptions = $module->canViewFullTimeStatistic()
  ? []
  : [
    'minDate' => new JsExpression("moment().subtract(3, 'month')"),
  ];
?>

<div class="filter_pos">
  <?= DateRangePicker::widget([
    'model' => $formModel,
    'attribute' => 'dateRange',
    'align' => DateRangePicker::ALIGN_LEFT,
    'pluginOptions' => $pluginOptions,
  ]);
  ?>
</div>