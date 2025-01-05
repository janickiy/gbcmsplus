<?php


use kartik\editable\Editable;
use kartik\widgets\DatePicker;
use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\promo\components\widgets\CountriesDropdown;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\models\ExternalProvider;
use mcms\promo\models\Landing;
use mcms\common\widget\Select2;
use mcms\promo\models\Service;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var \mcms\promo\models\search\CapSearch $searchModel */
/** @var \yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::_t('promo.caps.menu');
$pjaxId = 'caps-grid';
?>


<?php ContentViewPanel::begin([
  'padding' => false,
  'header' => false,
]) ?>
<?php Pjax::begin(['options' => ['id' => $pjaxId]]) ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'columns' => [
    [
      'attribute' => 'external_provider_id',
      'format' => 'raw',
      'filter' => ExternalProvider::getExternalProvidersMap(),
      'value' => function ($model, $key, $index) use ($pjaxId) {
        /** @var $model \mcms\promo\models\search\CapSearch */
        return $model->external_provider_id
          ? Editable::widget([
            'model' => $model->externalProvider,
            'attribute' => 'local_name',
            'asPopover' => true,
            'displayValue' => $model->externalProvider->getDisplayValue(),
            'value' => $model->externalProvider->local_name,
            'header' => Yii::_t('promo.caps.attribute-external_provider_id'),
            'pjaxContainerId' => $pjaxId,
            'size' => 'md',
            'formOptions' => [
              'action' => Url::to(['update-external-provider', 'id' => $model->external_provider_id])
            ],
            'pluginEvents' => [
              'editableSuccess' => "function(event, val, form, data) { $.pjax.reload($('#$pjaxId')); }",
            ],
            'options' => [
              'id' => 'external-provider-' . $index,
            ],
          ])
          : null;
      },
    ],
    [
      'attribute' => 'is_blocked',
      'class' => '\kartik\grid\BooleanColumn',
      'trueLabel' => Yii::_t('app.common.Yes'),
      'falseLabel' => Yii::_t('app.common.No'),
      'filterWidgetOptions' => [
        'pluginOptions' => [
          'allowClear' => true
        ],
        'options' => [
          'placeholder' => '',
        ],
      ],
    ],
    [
      'attribute' => 'active_from',
      'format' =>  'datetime',
      'filter' => DatePicker::widget([
        'model' => $searchModel,
        'attribute' => 'activeFrom1',
        'attribute2' => 'activeFrom2',
        'type' => DatePicker::TYPE_RANGE,
        'separator' => '<i class="glyphicon glyphicon-calendar"></i>',
        'pluginOptions' => [
          'format' => 'yyyy-mm-dd',
          'orientation' => 'bottom',
          'autoclose' => true,
        ]
      ])
    ],
    [
      'attribute' => 'operator_id',
      'format' => 'raw',
      'filter' => OperatorsDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'operator_id',
        'options' => [
          'placeholder' => '',
        ],
        'pluginOptions' => ['allowClear' => true],
        'useSelect2' => true
      ]),
      'value' => function ($model) {
        /** @var $model \mcms\promo\models\search\CapSearch */
        return $model->getOperatorLink();
      },
    ],
    [
      'attribute' => 'landing_id',
      'format' => 'raw',
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'landing_id',
        'data' => Landing::getLandingsByCategory(false),
        'options' => [
          'placeholder' => '',
        ],
        'pluginOptions'=> [
          'allowClear' => true,
        ]
      ]),
      'value' => 'landing.viewLink',
    ],
    [
      'attribute' => 'service_id',
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'service_id',
        'data' => Service::getServicesMap(),
        'options' => [
          'placeholder' => '',
        ],
        'pluginOptions'=> [
          'allowClear' => true,
        ]
      ]),
      'value' => 'service.name'
    ],
    [
      'attribute' => 'country_id',
      'format' => 'raw',
      'filter' => CountriesDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'country_id',
        'options' => [
          'placeholder' => '',
        ],
        'pluginOptions' => ['allowClear' => true],
        'useSelect2' => true
      ]),
      'value' => function ($model) {
        /** @var $model \mcms\promo\models\search\CapSearch */
        return $model->getCountryLink();
      },
    ],
    'day_limit',
  ],
]); ?>
<?php Pjax::end(); ?>

<?php ContentViewPanel::end() ?>
