<?php

use kartik\date\DatePicker;

use mcms\common\web\View;
use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\MassUpdateWidget;
use mcms\common\widget\modal\Modal;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingMassModel;
use rgk\utils\widgets\AjaxButton;
use yii\helpers\Url;
use yii\widgets\Pjax;
use mcms\common\helpers\Html;
use mcms\common\widget\Select2;
use mcms\promo\components\widgets\OperatorsDropdown;


/**
 * @var View $this
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var \yii\db\ActiveRecord $searchModel
 * @var bool $canEditAllProviders
 */

$canMassUpdate = Yii::$app->user->can('PromoLandingsMassUpdate');
$toolbar = $canMassUpdate ? MassUpdateWidget::widget([
  'model' => new LandingMassModel(['model' => new Landing]),
  'pjaxId' => '#landingsPjaxGrid',
  'viewPath' => '@mcms/promo/views/landings/_mass_update',
]) : null;
?>

<?php $this->beginBlock('actions'); ?>
<?= Html::a(Html::icon('plus') . ' ' . Yii::_t('promo.landings.create_external'), ['/promo/landings/create-external'], ['class' => 'btn btn-success',])?>
<?php $this->endBlock() ?>

<?= Html::beginTag('section',['id'=>'widget-grid']);
ContentViewPanel::begin([
    'padding' => false,
    'toolbar' => $toolbar,
]);
?>

<?php Pjax::begin(['id' => 'landingsPjaxGrid']); ?>

<?= AdminGridView::widget([
  'options' => ['style' => 'white-space:nowrap'], // TODO изменить на класс nowrap когда появится в проекте (пока в другой ветке находится)
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'rowOptions' => function ($model) {
    // Неактивный или заблокированный - красный
    if ($model->isDisabled()) {
      return ['class' => 'danger'];
    }
    // Активный и скрытый/по запросу - желтый
    if ($model->isEnabled() && $model->isHiddenByRequest()) {
      return ['class' => 'warning'];
    }

    return [];
  },
  'columns' => [
    [
      'class' => 'yii\grid\CheckboxColumn',
      'headerOptions' => ['style' => 'padding-right: 0; padding-left: 0 !important'],
      'visible' => $canMassUpdate,
    ],
    [
      'attribute' => 'id',
      'width' => '40px'
    ],
    [
      'attribute' => 'code',
      'format' => 'raw',
      'value' => function($model) {
        return $model->provider->code . $model->send_id;
      }
    ],
    [
      'attribute' => 'name',
      'format' => 'raw',
      'width' => '200px'
    ],
    [
      'attribute' => 'offer_category_id',
      'label' => Yii::_t('promo.landings.attribute-offer_category_id'),
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'offer_category_id',
        'data' => $searchModel->getOfferCategories(),
        'options' => [
            'placeholder' => '',
        ],
        'pluginOptions' => [
            'allowClear' => true,
        ]
      ]),
      'format' => 'raw',
      'value' => 'offerCategoryLink',
      'width' => '100px'
    ],
    [
      'attribute' => 'category.name',
      'label' => Yii::_t('promo.landings.attribute-category_id'),
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'category_id',
        'data' => $searchModel->getCategories(),
        'options' => [
            'placeholder' => '',
        ],
        'pluginOptions' => [
            'allowClear' => true,
        ]
      ]),
      'format' => 'raw',
      'value' => 'categoryLink',
      'width' => '100px'
    ],
    [
      'header' => Yii::_t('promo.landings.sets'),
      'format' => 'raw',
      'value' => 'setsLabel'
    ],
    [
      'attribute' => 'provider.name',
      'label' => Yii::_t('promo.landings.attribute-provider_id'),
      'visible' => Yii::$app->user->can('PromoProvidersViewModal'),
      'filter' => Select2::widget([
          'model' => $searchModel,
          'attribute' => 'provider_id',
          'data' => $searchModel->getProviders(),
          'options' => [
              'placeholder' => '',
          ],
          'pluginOptions' => [
              'allowClear' => true,
          ]
      ]),
      'format' => 'raw',
      'value' => function($model) {
        return Modal::widget([
          'toggleButtonOptions' => [
            'tag' => 'a',
            'label' => $model->provider->name,
            'data-pjax' => 0,
          ],
          'size' => Modal::SIZE_LG,
          'url' => Url::to(['/promo/providers/view-modal/', 'id' => $model->provider_id]),
        ]);
      },
      'width' => '130px'
    ],
    [
      'attribute' => 'countries',
      'format' => 'raw',
      'filter' => Select2::widget([
          'model' => $searchModel,
          'attribute' => 'countries',
          'data' => $countries,
          'options' => [
              'placeholder' => '',
              'multiple' => true
          ],
          'pluginOptions' => [
              'allowClear' => true
          ]
      ]),
      'value' => function($model) {
        return $model->gridCountries;
      },
      'width' => '100px'
    ],
    [
      'attribute' => 'operators',
      'format' => 'raw',
      'filter' => OperatorsDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'operators',
        'options' => [
          'prompt' => Yii::_t('app.common.not_selected'),
          'multiple' => true
        ],
        'countriesId' => $searchModel->countries,
        'useSelect2' => true,
      ]),
      'value' => function(Landing $model) {
        return $model->gridOperators;
      },
      'width' => '250px'
    ],
    [
      'attribute' => 'status',
      'filter' => $searchModel->getStatuses(),
      'value' => 'currentStatusName',
      'width' => '80px'
    ],
    [
      'attribute' => 'access_type',
      'filter' => $searchModel->getAccessTypes(),
      'value' => 'currentAccessTypeName',
      'width' => '80px'
    ],
    [
      'attribute' => 'created_at',
      'format' =>  'datetime',
      'filter' => DatePicker::widget([
        'model' => $searchModel,
        'attribute' => 'createdFrom',
        'attribute2' => 'createdTo',
        'type' => DatePicker::TYPE_RANGE,
        'separator' => '<i class="glyphicon glyphicon-calendar kv-dp-icon"></i>',
        'pluginOptions' => [
          'format' => 'yyyy-mm-dd',
          'orientation' => 'bottom',
          'autoclose' => true,
        ]
      ]),
      'contentOptions' => ['style' => 'width: 130px;']
    ],
//    'rating',
//    [
//      'attribute' => 'auto_rating',
//      'filter' => $searchModel->getAutoRatingTypes(),
//      'value' => 'currentAutoRatingTypeName'
//    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{copy-landing} {view-modal} {update} {disable} {enable} {delete}',
      'contentOptions' => ['class' => 'col-min-width-150'],
      'buttonOptions' => ['data-modal-max-width' => '1300px', 'data-modal-width' => '100%'],
      'buttons' => [
        'copy-landing' => function ($url, Landing $model) {

          $options = [
            'title' => Yii::t('yii', 'Copy'),
            'aria-label' => Yii::t('yii', 'Copy'),
            'data-confirm' => Yii::_t('partner_programs.are-you-sure-want-to-copy'),
            'data-pjax' => 0,
            'class' => 'btn btn-xs btn-default'
          ];

          return \yii\bootstrap\Html::a(
            \yii\bootstrap\Html::tag('i', '', ['class' => 'fa fa-copy']),
            ['landings/copy-landing', 'id' => $model->id],
            $options
          );
        },
        'view-modal' => function ($url, $model) {
          return Modal::widget([
            'toggleButtonOptions' => [
              'tag' => 'a',
              'label' => \yii\bootstrap\Html::icon('eye-open'),
              'title' => Yii::t('yii', 'View'),
              'class' => 'btn btn-xs btn-default',
              'data-pjax' => 0,
            ],
            'size' => Modal::SIZE_LG,
            'url' => $url,
          ]);
        },
        'delete' => function ($url, $model, $key) {
          $options = [
            'title' => Yii::t('yii', 'Delete'),
            'aria-label' => Yii::t('yii', 'Delete'),
            AjaxButton::CONFIRM_ATTRIBUTE => Yii::t('yii', 'Are you sure you want to delete this item?')
              . ' Будут удалены заявки на разблокировку, скрытые типы тарфика, связки с операторами и ссылками. Условия коррекции надо удалить вручную',
            'data-pjax' => 0,
            AjaxButton::RELOAD_ATTRIBUTE => 1,
            'class' => 'btn btn-xs btn-danger'
          ];
          return AjaxButton::widget(['options' => $options, 'text' => Html::icon('trash'), 'url' => $url]);
        },
      ],
      'visibleButtons' => [
        'enable' => function (Landing $model) {
          return $model->canChangeStatus();
        },
        'update' => function (Landing $model) use ($canEditAllProviders) {
          return $canEditAllProviders || !$model->provider->is_rgk;
        }
      ],
    ],

  ],
]); ?>
<?php Pjax::end(); ?>

<?php ContentViewPanel::end() ?>
<?= Html::endTag('section');?>
