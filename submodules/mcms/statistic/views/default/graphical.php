<?php

use admin\assets\SelectpickerAsset;
use mcms\common\grid\ContentViewPanel;
use mcms\common\helpers\Html;
use mcms\statistic\models\mysql\Statistic;
use mcms\statistic\assets\StatisticWidgetAsset;
use yii\helpers\Json;
use yii\widgets\Pjax;


StatisticWidgetAsset::register($this);
$charts = Json::encode(array_merge($data['quantity']['keys'], $data['finance']['keys']));
$this->registerJs(<<<JS
var selectPicker = $('#chart-select');
var columnsCookieKey = 'statistic_graph_columns';
var uncheckedColumnsCookieKey = 'statistic_graph_unchecked_columns';
Cookies.set(columnsCookieKey, $charts, {expires: 1});
selectPicker.selectpicker();

selectPicker.on('changed.bs.select', function(e) {
  var charts = $(this).val();
  var uncheckedVals = $(this).find('option').not('.hidden').not(':selected').map(function() {
    return $(this).val();
  }).toArray();
  Cookies.set(uncheckedColumnsCookieKey, uncheckedVals, {expires: 1});
  Cookies.set(columnsCookieKey, charts, {expires: 1});
});

selectPicker.on('hide.bs.select', function(e) {
  $('#statistic-filter-form').trigger('submit');
});

JS
);
/** @var Statistic $model */
/** @var \yii\web\View $this */
/** @var array $countriesId */
/** @var array $operatorsId */
/** @var array $landingIdList */
/** @var string $exportFileName */
/** @var \mcms\common\AdminFormatter $formatter */
?>

<div id="page-content-wrapper">
  <div class="container-fluid xyz">
    <?php
      $toolbar =  Html::dropDownList('chart-select', null, [
        Yii::_t('statistic.statistic.quantity_charts') => array_combine($data['quantity']['keys'], $data['quantity']['labels']),
        Yii::_t('statistic.statistic.finance_charts') => array_combine($data['finance']['keys'], $data['finance']['labels']),
      ], [
      'id' => 'chart-select',
      'class' => 'selectpicker menu-right col-i',
      'data-style' => 'btn-xs btn-success',
      'multiple' => true,
      'title' => yii\bootstrap\Html::icon('cog') . ' ' . Yii::_t('statistic.statistic.chart_select'),
      'data-count-selected-text' => yii\bootstrap\Html::icon('cog') . ' ' . Yii::_t('statistic.statistic.chart_select'),
      'data-selected-text-format' => 'count>1',
      'data-dropdown-align-right' => 1,
      ]);
    ?>
    <?php ContentViewPanel::begin([
      'padding' => false,
      'toolbar' => $toolbar,
    ]); ?>
    <div class="default-filters-block">
      <?= $this->render('_search', [
        'model' => $model,
        'dayHourGrouping' => $dayHourGrouping,
        'countriesId' => $countriesId,
        'countries' => $countries,
        'operatorsId' => $operatorsId,
        'landingsId' => $landingIdList,
        'filterDatePeriods' => isset($filterDatePeriods) ? $filterDatePeriods : null,
        'shouldHideGrouping' => $shouldHideGrouping,
      ]) ?>
    </div>
    <?php Pjax::begin(['id' => 'statistic-pjax']); ?>

    <div class="clearfix"></div>
    <div>

      <?= $this->render('_charts', [
        'model' => $model,
        'forceChangeGroup' => $forceChangeGroup,
        'chartSelectData' => $charts,
        'quantityData' => Json::encode($data['quantity']['data']),
        'quantityKeys' => Json::encode($data['quantity']['keys']),
        'quantityLabels' => Json::encode($data['quantity']['labels']),
        'financeData' => Json::encode($data['finance']['data']),
        'financeKeys' => Json::encode($data['finance']['keys']),
        'financeLabels' => Json::encode($data['finance']['labels']),
      ]) ?>
    </div>
    <div class="clearfix"></div>

    <?php Pjax::end(); ?>
    <?php ContentViewPanel::end() ?>
  </div>
</div>

