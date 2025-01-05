<?php

use kartik\date\DatePicker;
use mcms\api\models\Country;
use mcms\common\widget\UserSelect2;
use mcms\promo\models\Operator;
use mcms\statistic\components\FilterDropDownWidget;
use mcms\statistic\models\mysql\Analytics;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\JsExpression;

/** @var mcms\statistic\models\mysql\Analytics $model */
/** @var mcms\common\web\View $this */
/** @var array $countriesId */
/** @var array $operatorsId */
/** @var array $streamIdList */
$statModule = Yii::$app->getModule('statistic');
$filtersUrl = Url::to(['/statistic/analytics/filters/']);

$showDateOnFilter = isset($showDateOnFilter) ? $showDateOnFilter : false;
$showLtvDateFilter = isset($showLtvDateFilter) ? $showLtvDateFilter : false;
$showLtvDepthFilter = isset($showLtvDepthFilter) ? $showLtvDepthFilter : false;
$showIsVisibleToPartnerFilter = isset($showIsVisibleToPartnerFilter) ? $showIsVisibleToPartnerFilter : false;

$updateSelectValues = new JsExpression(<<<JS
  $(document).on('click',"#statisticCurrency button", function() {
    var currency = $(this).data('currency-code');
    $('#statistic-operators').val('').trigger('change');
    $('#statistic-countries').val('').trigger('change');
    $.ajax({
      url: '$filtersUrl',
      dataType: 'json',
      data: {
        'currency': currency
      }
    }).done(function(result) {
      var operatorSelect = $('#statistic-operators');
      var countrySelect = $('#statistic-countries');
      var countriesItems = result.data.countriesItems;
      var operatorItems = result.data.operatorItems;
      
      operatorSelect.empty();
      countrySelect.empty();
      
      $.each(countriesItems, function(value, key) {
          countrySelect.append($("<option></option>")
             .attr("value", value).text(key));
        });
        $(countrySelect).selectpicker('refresh');
      
      $.each(operatorItems, function(country, operators) {
        var group = $('<optgroup></optgroup>').attr('label', country);
        $.each(operators, function(operatorId, operatorName) {
          group.append($("<option></option>")
            .attr("value", operatorId).text(operatorName));
        });
        operatorSelect.append(group);
      });
      $(operatorSelect).selectpicker('refresh');
    });
  });
JS
);
$this->registerJs($updateSelectValues);

// Прячем фильтр по видимости, если тип != продажам
$typeSold = Analytics::SOLD;
$js = <<<JS
$('#statistic-type').change(function(){
  if($(this).val() === '$typeSold') {
    $('#isVisibleToPartner-filter').show();
  } else {
    $('#isVisibleToPartner-filter').hide();
  }
});
JS;
$this->registerJs($js);
?>

<?php if($model->canFilterByCurrency() && $widget = Yii::$app->getModule('promo')->api('mainCurrenciesWidget', [
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
  'layout' => 'inline',
  'options' => [
    'data-pjax' => true
  ],
  'id' => 'statistic-filter-form',
]); ?>
<div class="dt-toolbar">
  <div class="filter_pos">
    <?= Html::activeDropDownList(
      $model,
      'type',
      $model->getProfitTypeFilter(),
      [
        'class' => 'form-control auto_filter',
      ]
    )?>
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
  <?php if ($showDateOnFilter): ?>
    <div class="pull-right">
      <div class="filter_pos">
        <p style="margin: 7px 0 0;"><?= Yii::_t('statistic.statistic.subscribed_at') ?>:</p>
      </div>
      <div class="filter_pos">
        <?= DatePicker::widget([
          'model' => $model,
          'attribute' => 'date_on_from',
          'attribute2' => 'date_on_to',
          'type' => DatePicker::TYPE_RANGE,
          'separator' => '<i class="glyphicon glyphicon-calendar"></i>',
          'pluginOptions' => [
            'endDate' => $model->end_date ?: null,
            'format' => 'yyyy-mm-dd',
            'autoclose' => true,
            'orientation' => 'bottom'
          ],
          'options' => ['style' => 'width:130px'],
          'options2' => ['style' => 'width:130px'],
        ]); ?>
      </div>
    </div>
  <?php endif ?>
  <?php if ($showLtvDateFilter): ?>
    <div class="filter_pos">
      <?= DatePicker::widget([
        'model' => $model,
        'attribute' => 'date_ltv',
        'pickerButton' => false,
        'pluginOptions' => [
          "startDate" => $model->end_date ?: null,
          'format' => 'yyyy-mm-dd',
          'autoclose' => true,
          'orientation' => 'bottom'
        ],
        'options' => [
          'placeholder' => $model->getAttributeLabel('date_ltv'),
        ]
      ]); ?>
    </div>
  <?php endif ?>
  <?php if ($showLtvDepthFilter): ?>
      <div class="pull-right">
        <div class="filter_pos">
          <p style="margin: 7px 0 0;"><?= Yii::_t('statistic.statistic.ltv_depth') ?>:</p>
        </div>
        <div class="filter_pos">
          <?= $form->field($model, 'ltv_depth_from')->textInput([
              'type' => 'number',
            'placeholder' => Yii::_t('statistic.statistic.ltv_days_from')
          ]) ?>
          <?= $form->field($model, 'ltv_depth_to')->textInput([
              'type' => 'number',
            'placeholder' => Yii::_t('statistic.statistic.ltv_days_to')
          ]) ?>
        </div>
    </div>
  <?php endif ?>

  <?php if($model->canFilterByCurrency()): ?>
    <?= $form->field($model, 'currency')->hiddenInput([
      'id' => 'hiddenCurrency'
    ])->label(false); ?>
  <?php endif; ?>

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
        <?= $form->field($model, 'providers')->widget(FilterDropDownWidget::class,['items'=>$model->getProviders()]) ?>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php $this->beginBlockAccessVerifier('countries', ['StatisticFilterByCountries']); ?>
      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?php $country = ArrayHelper::map(Country::find()->where(['status' => Country::STATUS_ACTIVE])->all(), 'id', 'name');
        echo $form->field($model, 'countries')->widget(FilterDropDownWidget::class, ['items' => $country]) ?>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php $this->beginBlockAccessVerifier('operators', ['StatisticFilterByOperators']); ?>
      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?= $form->field($model, 'operators')->widget(FilterDropDownWidget::class, [
          'items' => Operator::getOperatorsDropDown([], true, false, [], false)
        ]) ?>
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
        <?= $form->field($model, 'streams')
          ->widget(Yii::$app->getModule('promo')->api('streamsDropdown')->getWidgetclass, [
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
        <?= $form->field($model, 'platforms')->widget(FilterDropDownWidget::class,['items'=>$model->getPlatforms()]) ?>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php if ($model->canViewHiddenSoldSubscriptions() && $showIsVisibleToPartnerFilter): ?>
        <div id="isVisibleToPartner-filter" class="col-sm-3 col-xs-6 margin-bottom-10" <?=$model->type !== Analytics::SOLD ? 'style="display: none;"' : '' ?>>
          <?= Html::activeDropDownList(
            $model,
            'is_visible_to_partner',
            $model->getVisibleStatuses(),
            [
              'class' => 'form-control',
              'prompt' => $model->getAttributeLabel('is_visible_to_partner'),
              'style' => 'width:100%;'
            ]
          ) ?>
        </div>
      <?php endif; ?>

        <div class="col-sm-5 pull-right"><label>&nbsp;</label>
            <div>
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
</div>


<?php ActiveForm::end(); ?>
