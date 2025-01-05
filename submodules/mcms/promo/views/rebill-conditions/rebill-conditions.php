<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\modal\Modal;
use mcms\common\widget\Select2;
use mcms\promo\assets\RebillConditionsAsset;
use mcms\promo\models\search\RebillConditionsSearch;
use yii\widgets\Pjax;
use yii\helpers\Url;
use kartik\helpers\Html;
use mcms\promo\Module;
use mcms\common\widget\UserSelect2;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\components\widgets\LandingsDropdown;

/** @var \yii\web\View $this */
/** @var integer $partnerId */
/** @var string $layout */
/** @var bool $renderCreateButton */
/** @var bool $enableFilters */
/** @var bool $renderActions */
/** @var bool $enableSort */
/** @var \yii\data\ActiveDataProvider $dataProvider */
/** @var RebillConditionsSearch $searchModel */

RebillConditionsAsset::register($this);
?>
<?= Html::beginTag('section',['id'=>'widget-grid']);
ContentViewPanel::begin([
  'padding' => false,
  'toolbar' => $renderCreateButton
    ? $this->render('_create_button', ['partnerId' => $partnerId])
    : null,
]);
?>

<?php Pjax::begin(['id' => 'rebill-conditions-pjax-block']); ?>
<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $enableFilters ? $searchModel : null,
  'sorter' => [],
  'export' => false,
  'columns' => [
    'id',
    [
      'attribute' => 'percent',
      'enableSorting' => $enableSort
    ],
    [
      'attribute' => 'partner_id',
      'filter' => UserSelect2::widget([
        'roles' => $userModule::PARTNER_ROLE,
        'model' => $searchModel,
        'options' => [
          'placeholder' => ''
        ],
        'attribute' => 'partner_id',
        'initValueUserId' => $searchModel->partner_id
      ]),
      'format' => 'raw',
      'value' => 'partnerLink',
      'enableSorting' => $enableSort,
      'visible' => !$partnerId,
    ],
    [
      'attribute' => 'operator_id',
      'format' => 'raw',
      'value' => 'operatorLink',
      'filter' => OperatorsDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'operator_id',
        'options' => [
          'placeholder' => ''
        ],
        'pluginOptions' => ['allowClear' => true],
        'useSelect2' => true
      ]),
      'enableSorting' => $enableSort
    ],
    [
      'attribute' => 'landing_id',
      'format' => 'raw',
      'value' => 'landingLink',
      'filter' => LandingsDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'landing_id',
        'options' => [
          'placeholder' => ''
        ],
        'pluginOptions' => ['allowClear' => true],
        'useSelect2' => true
      ]),
      'enableSorting' => $enableSort
    ],
    [
      'attribute' => 'provider_id',
      'format' => 'raw',
      'value' => 'providerLink',
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'provider_id',
        'theme' => Select2::THEME_SMARTADMIN,
        'data' => RebillConditionsSearch::getProvidersDropdown(),
        'options' => [
          'placeholder' => '',
          'class' => 'form-control selectpicker operators-selectpicker',
          'data-width' => '100%',
          'data-live-search' => 'true',
          'style' => 'width:100%'
        ],
        'pluginOptions' => ['allowClear' => true],
      ]),
      'enableSorting' => $enableSort
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{update-modal} {delete}',
      'controller' => 'promo/rebill-conditions',
      'contentOptions' => ['class' => 'col-min-width-100'],
      'buttons' => [
        'update-modal' => function ($url, $model) use ($partnerId) {
          return Modal::widget([
            'toggleButtonOptions' => [
              'tag' => 'a',
              'title' => Yii::t('yii', 'Update'),
              'label' => Html::icon('pencil'),
              'class' => 'btn btn-xs btn-default',
              'data-pjax' => 0,
            ],
            'url' => ['/' . Module::getInstance()->id . '/rebill-conditions/update-modal', 'id' => $model->id, 'isPersonal' => !!$partnerId]
          ]);
        }
      ],
      'visible' => $renderActions
    ],
  ],
]); ?>
<?php Pjax::end(); ?>

<?php ContentViewPanel::end() ?>
<?= Html::endTag('section');?>
