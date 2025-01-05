<?php
use mcms\statistic\models\resellerStatistic\ItemSearch;
use rgk\utils\widgets\DateRangePicker;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\web\View;

/** @var View $this */
/** @var ItemSearch $searchModel */
?>

<?php $form = ActiveForm::begin([
  'method' => 'get',
//  'layout' => 'inline',
  'options' => ['class' => 'dt-toolbar header-filters'],
  'id' => 'statistic-filter-form',
  'action' => ['index'],
  'fieldConfig' => [
    'template' => '{input}',
  ],
]); ?>
<?= Html::activeHiddenInput($searchModel, 'groupType', ['id' => 'statistic-group-type']); ?>
<div class="row">
  <div class="col-xs-8 col-sm-4 col-md-4 col-lg-3">
    <?= $form->field($searchModel, 'dateRange')->widget(DateRangePicker::class); ?>
  </div>
  <div class="col-sm-2 pull-right">
    <?= Html::submitButton(Yii::_t('statistic.reseller_profit.apply_filter'), ['class' => 'btn btn-info pull-right']) ?>
  </div>
</div>
<?php ActiveForm::end(); ?>
<div class="clearfix"></div>