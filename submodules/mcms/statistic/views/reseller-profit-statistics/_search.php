<?php

use kartik\form\ActiveForm;
use kartik\date\DatePicker;
use yii\bootstrap\Html;
use mcms\statistic\components\FilterDropDownWidget;
use mcms\common\widget\UserSelect2;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

/** @var \mcms\statistic\models\mysql\Statistic $model */
/** @var mcms\common\web\View $this */

$statModule = Yii::$app->getModule('statistic');
?>

<?php if($widget = Yii::$app->getModule('promo')->api('mainCurrenciesWidget', [
  'type' => 'buttons',
  'containerId' => 'statisticCurrency'
])->getResult()): ?>
  <?php $this->beginBlock('actions'); ?>

  <?= $widget ?>

  <?php $this->registerJs('
    $("#statisticCurrency").on("mainCurrencyChanged", function(e, newValue){
      $("#hiddenCurrency").val(newValue).closest("form").trigger("submit");
    });
  ')?>

  <?php $this->endBlock() ?>
<?php endif; ?>


<?php $form = ActiveForm::begin([
  'method' => 'GET',
  'action' => ['/' . Yii::$app->controller->getRoute()],
  'type' => ActiveForm::TYPE_INLINE,
  'options' => [
    'data-pjax' => true,
    'id' => 'statistic-filter-form',
  ],
]); ?>
<div class="dt-toolbar">
  <div class="filter_pos filter_pos_flex">
    <?php
    $options = [
      'class' => 'form-control auto_filter statistic-group-filter',
      'value' => $model->group,
    ];
    ?>
    <?= Html::activeDropDownList(
      $model,
      'group',
      $model->groups,
      $options
    ) ?>
  </div>
  <div class="filter_pos">
    <?php if (isset($filterDatePeriods)): ?>
      <div class="btn-group" role="group" aria-label="..." style="width: 100%;">
        <?php if (ArrayHelper::getValue($filterDatePeriods, 'today')): ?>
          <button type="button" class="btn btn-default filter-button<?php if (!ArrayHelper::getValue($filterDatePeriods, 'week')):?> active<?php endif; ?>"
                  data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'today.from') ?>"
                  data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'today.to') ?>"
                  data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'today.from') ?>"
                  data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'today.to') ?>"
          ><?= Yii::_t('statistic.statistic.filter_date_today') ?></button>
        <?php endif ?>

        <?php if (ArrayHelper::getValue($filterDatePeriods, 'yesterday')): ?>
          <button type="button" class="btn btn-default filter-button"
                  data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.from') ?>"
                  data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.to') ?>"
                  data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.from') ?>"
                  data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.to') ?>"
          ><?= Yii::_t('statistic.statistic.filter_date_yesterday') ?></button>
        <?php endif ?>

        <?php if (ArrayHelper::getValue($filterDatePeriods, 'week')): ?>
          <button type="button" class="btn btn-default filter-button active"
                  data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'week.from') ?>"
                  data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'week.to') ?>"
                  data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'week.from') ?>"
                  data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'week.to') ?>"
          ><?= Yii::_t('statistic.statistic.filter_date_week') ?></button>
        <?php endif ?>

        <?php if (ArrayHelper::getValue($filterDatePeriods, 'month')): ?>
          <button type="button" class="btn btn-default filter-button"
                  data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'month.from') ?>"
                  data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'month.to') ?>"
                  data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'month.from') ?>"
                  data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'month.to') ?>"
          ><?= Yii::_t('statistic.statistic.filter_date_month') ?></button>
        <?php endif ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="filter_pos">

    <?= DatePicker::widget([
      'model' => $model,
      'attribute' => 'start_date',
      'attribute2' => 'end_date',
      'type' => DatePicker::TYPE_RANGE,
      'separator' => '<i class="glyphicon glyphicon-calendar"></i>',
      'pluginOptions' => [
        'format' => 'yyyy-mm-dd',
        'autoclose' => true,
        'orientation' => 'bottom'
      ],
      'options' => ['style' => 'width:130px'],
      'options2' => ['style' => 'width:130px'],
      'pluginEvents' => [
        'changeDate' => 'function(e) {
          var $date_ltv = $("#statistic-date_ltv");
          if (!$date_ltv.length) return false;
          
          var end_date = $("#statistic-end_date").kvDatepicker("getDate");
          var date_ltv_val = $date_ltv.val() == "" ? false : new Date($date_ltv.val());
          
          $date_ltv.kvDatepicker({ format: "yyyy-mm-dd", autoclose: true, language: "' . Yii::$app->language . '" });
          $date_ltv.kvDatepicker("setStartDate", end_date);
          
          if (date_ltv_val && date_ltv_val < end_date) {
            $date_ltv.kvDatepicker("update", end_date);
          }

        }',
      ]
    ]); ?>

  </div>

  <?= $form->field($model, 'currency')->hiddenInput([
    'id' => 'hiddenCurrency'
  ])->label(false); ?>

  <div class="filter_pos">

    <?= Html::button(Html::icon('filter') . ' ' . Yii::_t('statistic.statistic.filters'), [
      'class' => 'btn btn-default',
      'data-toggle' => 'collapse',
      'href' => '#hidden-filters',
      'aria-expanded' => 'false',
      'aria-controls' => 'collapseExample',
    ])?>

  </div>

  <div class="clearfix"></div>
</div>
<div class="collapse" id="hidden-filters">
  <div class="well">
    <div class="row">
      <?php $this->beginBlockAccessVerifier('landing_pay_types', ['StatisticFilterByLandingPayTypes']); ?>
      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?= Html::activeDropDownList(
          $model,
          'landing_pay_types',
          $model->getLandingPayTypes(),
          [
            'class' => 'form-control',
            'prompt' => $model->getAttributeLabel('landing_pay_types'),
            'style' => 'width:100%;'
          ]
        )?>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php $this->beginBlockAccessVerifier('providers', ['StatisticFilterByProviders']); ?>
      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?= $form->field($model, 'providers')->widget(FilterDropDownWidget::class, ['items' => $model->getProviders()]) ?>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php $this->beginBlockAccessVerifier('countries', ['StatisticFilterByCountries']); ?>
      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?= $form->field($model, 'countries')->widget(FilterDropDownWidget::class, ['items' => $countries]) ?>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php $this->beginBlockAccessVerifier('operators', ['StatisticFilterByOperators']); ?>
      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?= $form->field($model, 'operators')->widget(Yii::$app->getModule('promo')->api('operatorsDropdown')
          ->getWidgetclass, [
          'onlyActiveCountries' => false,
          'countriesId' => $countriesId,
          'operatorsId' => $operatorsId,
          'options' => [
            'title' => $model->getAttributeLabel('operators'),
            'prompt' => null,
            'multiple' => true,
            'data-selected-text-format' => 'count>0',
            'data-count-selected-text' => $model->getAttributeLabel('operators'). ' ({0}/{1})',
          ]]) ?>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php $this->beginBlockAccessVerifier('users', ['StatisticFilterByUsers', 'StatisticStatFiltersUsers']); ?>
      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?= UserSelect2::widget([
          'model' => $model,
          'url' => ['stat-filters/users'],
          'options' => [
            'placeholder' => Yii::_t('statistic.users'),
            'multiple' => true,
          ],
          'attribute' => 'users',
          'initValueUserId' => $model->users,
        ]) ?>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php $this->beginBlockAccessVerifier('streams', ['StatisticFilterByStreams', 'StatisticStatFiltersStreams']); ?>
      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?= $form->field($model, 'streams')->widget(Yii::$app->getModule('promo')->api('streamsDropdown')->getWidgetclass, [
          'initValueId' => $model->streams,
          'url' => Url::to(['stat-filters/streams']),
          'options' => [
            'placeholder' => Yii::_t('statistic.streams'),
            'multiple' => true,
          ]]) ?>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php $this->beginBlockAccessVerifier('sources', ['StatisticFilterBySources', 'StatisticStatFiltersSources']); ?>
      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?= $form->field($model, 'sources')->widget(Yii::$app->getModule('promo')->api('sourcesDropdown')->getWidgetclass, [
          'initValueId' => $model->sources,
          'url' => Url::to(['stat-filters/sources']),
          'options' => [
            'placeholder' => Yii::_t('statistic.sources'),
            'multiple' => true,
          ]]) ?>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php $this->beginBlockAccessVerifier('landings', ['StatisticFilterByLandings']); ?>
      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?= $form->field($model, 'landings')
          ->widget(Yii::$app->getModule('promo')->api('ajaxLandingsDropdown')->getWidgetclass, [
            'url' => Url::to(['/promo/landings/stat-filters-select2/']),
            'initValueId' => [],
            'options' => [
              'placeholder' => $model->getAttributeLabel('landings'),
              'multiple' => true,
            ]]) ?>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php $this->beginBlockAccessVerifier('platforms', ['StatisticFilterByPlatforms']); ?>
      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?= $form->field($model, 'platforms')->widget(FilterDropDownWidget::class, ['items' => $model->getPlatforms()]) ?>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <div class="col-sm-5 pull-right">
        <?= Html::submitButton(Yii::_t('statistic.filter_submit'), ['class' => 'btn btn-info pull-right'])?>
        <?= Html::a(
          Yii::_t('statistic.filter_reset'),
          Url::to(['/' . Yii::$app->controller->getRoute()]),
          ['class' => 'btn btn-default pull-right', 'style' => 'margin-right: 10px']
        ) ?>
      </div>

    </div>

  </div>
</div>

<?php ActiveForm::end(); ?>
