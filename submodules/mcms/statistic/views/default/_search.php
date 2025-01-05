<?php

use kartik\form\ActiveForm;
use mcms\statistic\components\DatePeriod;
use mcms\statistic\Module;
use kartik\date\DatePicker;
use yii\bootstrap\Html;
use mcms\statistic\components\FilterDropDownWidget;
use mcms\common\widget\UserSelect2;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use mcms\statistic\assets\StatisticGroupFiltersAsset;

/** @var \mcms\statistic\models\mysql\Statistic $model */
/** @var mcms\common\web\View $this */
/** @var array $countriesId */
/** @var array $operatorsId */
/** @var array $streamIdList */
/** @var Module $statModule */
StatisticGroupFiltersAsset::register($this);

$statModule = Yii::$app->getModule('statistic');
$maxGroups = 2;
$this->registerJs(/** @lang JavaScript */ "STATISTIC_MAX_GROUPS = $maxGroups;", $this::POS_HEAD);
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
  'action' => ['/' . Yii::$app->controller->getRoute()],
  'type' => ActiveForm::TYPE_INLINE,
  'options' => [
    'data-pjax' => true,
    'id' => 'statistic-filter-form',
  ],
]); ?>
<div class="dt-toolbar">
  <div class="filters-left">
    <div class="filter_pos">

      <?= Html::activeDropDownList(
        $model,
        'revshareOrCPA',
        $model->getRevshareOrCpaFilter(),
        [
          'class' => 'form-control auto_filter',
        ]
      )?>

    </div>

    <?php if (!$shouldHideGrouping): ?>
      <div class="filter_pos filter_pos_flex">
        <?php $i = 0; ?>
        <?php foreach ($model->group ?: [null] as $value) { ?>
          <?php $i++; ?>
          <?php
          $options = [
            'class' => 'form-control auto_filter statistic-group-filter',
            'value' => $value,
          ];
          if ($i > 1) {
            $options['prompt'] = [
              'text' => Yii::_t('statistic.statistic.remove_filter_group'),
              'options' => ['value' => '', 'class' => 'prompt', 'label' => Yii::_t('statistic.statistic.remove_filter_group')]
            ];
          }
          ?>
          <?= Html::activeDropDownList(
            $model,
            'group',
            $model->getGroupsBy([
              'date',
              'month_number',
              'week_number',
              'landings',
              'webmasterSources',
              'arbitraryLinks',
              'streams',
              'platforms',
              'operators',
              'countries',
              'providers',
              'users',
              'landing_pay_types',
              'invest_users',
              'invest_streams',
              'invest_sources',
              'managers',
            ]),
            $options
          ) ?>
        <?php } ?>
        <?php if (count($model->group) < $maxGroups) { ?>
            <button type="button" class="btn btn-default" id="add-group">+</button>
        <?php } ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="filters-right">
    <?php if (!empty($dayHourGrouping)): ?>
      <div class="filter_pos">
        <?= Html::activeDropDownList(
          $model,
          'group',
          Yii::$app->user->can('StatisticGroupByHours') ? $model->getGroupsBy([
            'date',
            'date_hour',
          ]) : $model->getGroupsBy(['date']),
          ['class' => 'form-control auto_filter']
        )?>
      </div>
    <?php endif; ?>

    <div class="filter_pos">
      <?php if (isset($filterDatePeriods)): ?>
        <?= $form->field($model, 'period', ['options' => ['class' => '']])
          ->hiddenInput(['id' => 'statistic-period'])->label(false) ?>

        <div class="btn-group" role="group" aria-label="..." style="width: 100%;">
          <?php if (ArrayHelper::getValue($filterDatePeriods, 'today')): ?>
                    <button data-period="<?= DatePeriod::PERIOD_TODAY ?>" type="button" class="btn btn-default filter-button
            <?= $model->period == DatePeriod::PERIOD_TODAY ? ' active' : null ?>"
                    data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'today.from') ?>"
                    data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'today.to') ?>"
                    data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'today.from') ?>"
                    data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'today.to') ?>"
            ><?= Yii::_t('statistic.statistic.filter_date_today') ?></button>
          <?php endif ?>

          <?php if (ArrayHelper::getValue($filterDatePeriods, 'yesterday')): ?>
                    <button data-period="<?= DatePeriod::PERIOD_YESTERDAY ?>" type="button" class="btn btn-default filter-button
            <?= $model->period == DatePeriod::PERIOD_YESTERDAY ? ' active' : null ?>"
                    data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.from') ?>"
                    data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.to') ?>"
                    data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.from') ?>"
                    data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.to') ?>"
            ><?= Yii::_t('statistic.statistic.filter_date_yesterday') ?></button>
          <?php endif ?>

          <?php if (ArrayHelper::getValue($filterDatePeriods, 'week')): ?>
                    <button data-period="<?= DatePeriod::PERIOD_LAST_WEEK ?>" type="button" class="btn btn-default filter-button
            <?= $model->period == DatePeriod::PERIOD_LAST_WEEK ? ' active' : null ?>"
                    data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'week.from') ?>"
                    data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'week.to') ?>"
                    data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'week.from') ?>"
                    data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'week.to') ?>"
            ><?= Yii::_t('statistic.statistic.filter_date_week') ?></button>
          <?php endif ?>

          <?php if (ArrayHelper::getValue($filterDatePeriods, 'month')): ?>
                    <button data-period="<?= DatePeriod::PERIOD_LAST_MONTH ?>" type="button" class="btn btn-default filter-button
            <?= $model->period == DatePeriod::PERIOD_LAST_MONTH ? ' active' : null ?>"
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
          'options2' => ['style' => 'width:130px']
        ]); ?>
      </div>

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
              'initValueId' => $model->landings,
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

      <?php $this->beginBlockAccessVerifier('filter_by_fake', ['StatisticFilterByFakeRevshare']) ?>
      <div class="col-sm-3">
        <?= $form->field($model, 'isFake')->widget(FilterDropDownWidget::class, ['items' => [
          Yii::_t('statistic.statistic.is_fake_no'),
          Yii::_t('statistic.statistic.is_fake_yes'),
        ]]) ?>
      </div>
      <?php $this->endBlockAccessVerifier() ?>

    </div>

  </div>
</div>

<?php ActiveForm::end(); ?>
