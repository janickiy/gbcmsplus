<?php

use kartik\form\ActiveForm;
use kartik\date\DatePicker;
use mcms\common\widget\Select2;
use mcms\statistic\Module;
use yii\bootstrap\Html;
use mcms\statistic\components\FilterDropDownWidget;
use mcms\common\widget\UserSelect2;
use yii\helpers\Url;
use yii\web\JsExpression;

/** @var \mcms\statistic\models\mysql\DetailStatistic $model */
/** @var mcms\common\web\View $this */
/** @var array $countriesId */
/** @var array $operatorsId */
/** @var array $streamIdList */
/** @var \mcms\statistic\models\mysql\DetailStatisticSubscriptions $statisticModel */
$statisticModel = $model->getStatisticModel();
/** @var Module $statModule */
$statModule = Yii::$app->getModule('statistic');

$datepickerPluginOptions = [
  'format' => 'yyyy-mm-dd',
  'autoclose' => true,
  'orientation' => 'bottom',
];
$statModule->canViewFullTimeStatistic() || $datepickerPluginOptions['startDate'] = '-3m';
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
  'type' => ActiveForm::TYPE_INLINE,
  'options' => [
    'data-pjax' => true,
    'id' => 'statistic-filter-form',
  ]
]); ?>
<div class="dt-toolbar">
  <div class="filter_pos">
    <?= $this->render('_group_buttons', [
      'groups' => $model->getGroups(),
      'currentGroup' => $currentGroup
    ]); ?>
  </div>
  <div class="filter_pos">
    <?= DatePicker::widget([
      'model' => $model->getStatisticModel(),
      'attribute' => 'start_date',
      'attribute2' => 'end_date',
      'type' => DatePicker::TYPE_RANGE,
      'separator' => '<i class="glyphicon glyphicon-calendar"></i>',
      'pluginOptions' => $datepickerPluginOptions,
    ]); ?>
  </div>

  <?php if($model->canFilterByCurrency()): ?>
    <?= $form->field($model, 'currency')->hiddenInput([
      'id' => 'hiddenCurrency'
    ])->label(false); ?>
  <?php endif; ?>

  <div class="filter_pos">

    <div class="btn-group" data-toggle="buttons">
      <label class="btn btn-default"
             data-toggle="collapse"
             href="#hidden-filters"
             aria-expanded="false"
             aria-controls="collapseExample">
        <?= Html::icon('filter') . ' ' . Yii::_t('statistic.statistic.filters') ?>
      </label>
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
          $statisticModel,
          'landing_pay_types',
          $model->getLandingPayTypes(),
          [
            'class' => 'form-control',
            'prompt' => $statisticModel->getAttributeLabel('landing_pay_types'),
            'style' => 'width:100%;'
          ]
        )?>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php $this->beginBlockAccessVerifier('providers', ['StatisticFilterByProviders']); ?>
      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?= $form->field($statisticModel, 'providers')->widget(FilterDropDownWidget::class,['items'=>$model->getProviders()]) ?>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php $this->beginBlockAccessVerifier('countries', ['StatisticFilterByCountries']); ?>
      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?= $form->field($statisticModel, 'countries')->widget(FilterDropDownWidget::class,['items' => $countries]) ?>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php $this->beginBlockAccessVerifier('operators', ['StatisticFilterByOperators']); ?>
      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?= $form->field($statisticModel, 'operators')->widget(Yii::$app->getModule('promo')->api('operatorsDropdown')
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
          'model' => $statisticModel,
          'url' => ['stat-filters/users'],
          'options' => [
            'placeholder' => Yii::_t('statistic.users'),
            'multiple' => true,
          ],
          'attribute' => 'users',
          'initValueUserId' => $statisticModel->users,
        ]) ?>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php $this->beginBlockAccessVerifier('streams', ['StatisticFilterByStreams', 'StatisticStatFiltersStreams']); ?>
      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?= $form->field($statisticModel, 'streams')
          ->widget(Yii::$app->getModule('promo')->api('streamsDropdown')->getWidgetclass, [
            'initValueId' => $statisticModel->streams,
            'url' => Url::to(['stat-filters/streams']),
            'options' => [
              'placeholder' => Yii::_t('statistic.streams'),
              'multiple' => true,
            ]]) ?>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php $this->beginBlockAccessVerifier('sources', ['StatisticFilterBySources', 'StatisticStatFiltersSources']); ?>
      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?= $form->field($statisticModel, 'sources')
          ->widget(Yii::$app->getModule('promo')->api('sourcesDropdown')->getWidgetclass, [
            'initValueId' => $statisticModel->sources,
            'url' => Url::to(['stat-filters/sources']),
            'options' => [
              'placeholder' => Yii::_t('statistic.sources'),
              'multiple' => true,
            ]]) ?>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php $this->beginBlockAccessVerifier('landings', ['StatisticFilterByLandings']); ?>
        <div class="col-sm-3 col-xs-6 margin-bottom-10">
          <?= $form->field($statisticModel, 'landings')
            ->widget(Yii::$app->getModule('promo')->api('ajaxLandingsDropdown')->getWidgetclass, [
              'initValueId' => $statisticModel->landings,
              'url' => Url::to(['/promo/landings/stat-filters-select2/']),
              'options' => [
                'placeholder' => $model->getAttributeLabel('landings'),
                'multiple' => true,
              ]]) ?>
        </div>
      <?php $this->endBlockAccessVerifier(); ?>


      <?php $this->beginBlockAccessVerifier('platforms', ['StatisticFilterByPlatforms']); ?>
      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?= $form->field($statisticModel, 'platforms')->widget(FilterDropDownWidget::class,['items'=>$model->getPlatforms()]) ?>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php if ($model->canViewHiddenSoldSubscriptions()): ?>
        <div class="col-sm-3 col-xs-6 margin-bottom-10">
          <?= Html::activeDropDownList(
            $statisticModel,
            'is_visible_to_partner',
            $statisticModel->getVisibleStatuses(),
            [
              'class' => 'form-control',
              'prompt' => $statisticModel->getAttributeLabel('is_visible_to_partner'),
              'style' => 'width:100%;'
            ]
          ) ?>
        </div>
      <?php endif; ?>

      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?= Html::activeDropDownList(
          $statisticModel,
          'profit_type',
          $statisticModel->getProfitTypes(),
          [
            'class' => 'form-control',
            'prompt' => $statisticModel->getAttributeLabel('profit_type'),
            'style' => 'width:100%;'
          ]
        ) ?>
      </div>

      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?= $form->field($statisticModel, 'hit_id')->textInput(['style' => 'width:100%']) ?>
      </div>

      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?= $form->field($statisticModel, 'phone_number')->textInput(['style' => 'width:100%'])?>
      </div>

      <div class="col-sm-3 col-xs-6 margin-bottom-10">
        <?= $form->field($statisticModel, 'referer')->textInput(['style' => 'width:100%'])?>
      </div>

    </div>
    <div class="row">
      <div class="col-sm-6 col-lg-3">
        <label><?= Yii::_t('statistic.statistic.filter_rebill-date') ?></label>
        <?= DatePicker::widget([
          'model' => $statisticModel,
          'attribute' => 'rebillDateFrom',
          'attribute2' => 'rebillDateTo',
          'type' => DatePicker::TYPE_RANGE,
          'layout' => '<span class="input-group-addon">' . Yii::_t('statistic.statistic.filter-from') . '</span>' .
            '{input1}{separator}{input2}' .
            '<span class="input-group-addon kv-date-remove"><i class="glyphicon glyphicon-remove"></i></span>',
          'separator' => Yii::_t('statistic.statistic.filter-to'),
          'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
            'autoclose' => true,
            'orientation' => 'bottom',
          ],
        ]); ?>
      </div>
      <div class="col-sm-6 col-lg-3">
        <label><?= Yii::_t('statistic.statistic.filter_subscribe-date') ?></label>
        <?= DatePicker::widget([
          'model' => $statisticModel,
          'attribute' => 'subscribeDateFrom',
          'attribute2' => 'subscribeDateTo',
          'type' => DatePicker::TYPE_RANGE,
          'layout' => '<span class="input-group-addon">' . Yii::_t('statistic.statistic.filter-from') . '</span>' .
            '{input1}{separator}{input2}' .
            '<span class="input-group-addon kv-date-remove"><i class="glyphicon glyphicon-remove"></i></span>',
          'separator' => Yii::_t('statistic.statistic.filter-to'),
          'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
            'autoclose' => true,
            'orientation' => 'bottom'
          ],
        ]); ?>
      </div>
      <div class="col-sm-6 col-lg-3">
        <label><?= Yii::_t('statistic.statistic.filter_unsubscribe-date') ?></label>
        <?= DatePicker::widget([
          'model' => $statisticModel,
          'attribute' => 'unsubscribeDateFrom',
          'attribute2' => 'unsubscribeDateTo',
          'type' => DatePicker::TYPE_RANGE,
          'layout' => '<span class="input-group-addon">' . Yii::_t('statistic.statistic.filter-from') . '</span>' .
            '{input1}{separator}{input2}' .
            '<span class="input-group-addon kv-date-remove"><i class="glyphicon glyphicon-remove"></i></span>',
          'separator' => Yii::_t('statistic.statistic.filter-to'),
          'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
            'autoclose' => true,
            'orientation' => 'bottom',
          ],
        ]); ?>
      </div>
      <div class="col-sm-6 col-lg-3">
        <label><?= Yii::_t('statistic.statistic.filter_rebill-sum') ?></label>
        <?= \kartik\field\FieldRange::widget([
          'model' => $model->getStatisticModel(),
          'attribute1' => 'debitSumFrom',
          'attribute2' => 'debitSumTo',
          'type' => \kartik\field\FieldRange::INPUT_TEXT,
          'template' => '{widget}',
          'separator' => '-',
          'fieldConfig1' => [
            'autoPlaceholder' => false
          ],
          'fieldConfig2' => [
            'autoPlaceholder' => false
          ],
          'options' => [
            'style' => 'width:100%' // почему-то у некоторых поля не растягиваются по ширине
          ],
        ]); ?>
      </div>
      <div class="col-sm-6 col-lg-3">
        <label><?= Yii::_t('statistic.statistic.filter_rebill-count') ?></label>
        <?= \kartik\field\FieldRange::widget([
          'model' => $model->getStatisticModel(),
          'attribute1' => 'rebillCountFrom',
          'attribute2' => 'rebillCountTo',
          'type' => \kartik\field\FieldRange::INPUT_TEXT,
          'template' => '{widget}',
          'separator' => '-',
          'fieldConfig1' => [
            'autoPlaceholder' => false
          ],
          'fieldConfig2' => [
            'autoPlaceholder' => false
          ],
          'options' => [
            'style' => 'width:100%' // почему-то у некоторых поля не растягиваются по ширине
          ],
        ]); ?>

      </div>
      <div class="col-sm-3 col-xs-6 margin-bottom-10 margin-top-10">
        <?= $form->field($statisticModel, 'groupByPhone', ['options' => ['class' => 'margin-top-10']])->checkbox(); ?>
      </div>
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
