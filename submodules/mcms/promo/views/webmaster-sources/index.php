<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use kartik\editable\Editable;
use kartik\editable\EditableAsset;
use kartik\editable\EditablePjaxAsset;
use kartik\popover\PopoverXAsset;
use mcms\common\widget\modal\Modal;
use mcms\common\widget\UserSelect2;
use mcms\promo\assets\WebmasterSourcesIndexAssets;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\models\LandingSet;
use mcms\promo\models\Source;
use kartik\date\DatePicker;
use yii\helpers\Url;
use yii\widgets\ActiveFormAsset;
use yii\widgets\Pjax;
use mcms\common\helpers\ArrayHelper;
use yii\bootstrap\Html;
use mcms\common\widget\Select2;
use mcms\common\helpers\Html as OurHtml;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var mcms\promo\models\search\SourceSearch $searchModel
 */
$this->title = Yii::_t('webmaster_sources.main');
WebmasterSourcesIndexAssets::register($this);
EditableAsset::register($this);
EditablePjaxAsset::register($this);
PopoverXAsset::register($this);
ActiveFormAsset::register($this);

$editableCategoryColumn =     [
  'attribute' => 'category_id',
  'value' => function($model) {
    return $model->getCurrentCategoryName();
  },
  'filter' => Select2::widget([
    'model' => $searchModel,
    'attribute' => 'category_id',
    'data' => $searchModel->getCategories(),
    'options' => [
      'placeholder' => '',
    ],
    'pluginOptions' => [
      'allowClear' => true,
      'width' => '120px'
    ]
  ]),
];
if (OurHtml::hasUrlAccess(['/promo/webmaster-sources/update-category/'])) {
  $editableCategoryColumn['class'] = 'kartik\grid\EditableColumn';
  $editableCategoryColumn['editableOptions'] = function ($model, $key, $index) use ($searchModel){
    return [
      'pjaxContainerId' => 'webmaster-sources-pjax',
      'inputType' => Editable::INPUT_SELECT2,
      'formOptions' => [
        'action' => Url::to(['update-category', 'id' => $model->id])
      ],
      'options' => [
        'data' => $searchModel->getCategories(),
        'theme' => Select2::THEME_SMARTADMIN
      ]
    ];
  };
}
?>

<?= Html::beginTag('section',['id'=>'widget-grid']);
ContentViewPanel::begin([
    'padding' => false,
]);
?>

<?php Pjax::begin(['id' => 'webmaster-sources-pjax']); ?>
<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'id' => 'webmasterSourcesGrid',
  'rowOptions' => function ($model) { /* @var Source $model */
    return ['class' => ArrayHelper::getValue($model::getStatusColors(), $model->status, '')];
  },
  'export' => false,
  'columns' => [
    [
      'attribute' => 'id',
      'width' => '30px',
    ],
    [
      'class' => 'kartik\grid\ExpandRowColumn',
      'header' => false,
      'allowBatchToggle' => false,
      'value' => function ($model, $key, $index, $column) {
        return AdminGridView::ROW_COLLAPSED;
      },
      'detailUrl' => Url::to(['view']),
      'detailOptions'=>[
        'class'=> 'kv-state-enable',
      ],
      'visible' => OurHtml::hasUrlAccess(['/promo/webmaster-sources/view']),
    ],
    [
      'attribute' => 'url',
      'format' => [
        'url', [
          'target' => '_blank'
        ]
      ],
      'contentOptions'=>['style'=>'max-width: 180px;word-wrap: break-word;']
    ],
    [
      'attribute' => 'hash',
    ],
    [
      'attribute' => 'user_id',
      'vAlign'=> 'middle',
      'format' => 'raw',
      'width' => '200px',
      'filter' => UserSelect2::widget([
          'model' => $searchModel,
          'attribute' => 'user_id',
          'initValueUserId' => $searchModel->user_id,
          'options' => [
              'placeholder' => '',
          ],
        ]
      ),
      'value' => 'userLink',
      'enableSorting' => false,
      'contentOptions' => ['style'=>'max-width: 200px; overflow: auto; word-wrap: break-word;'],
    ],
    $editableCategoryColumn,
    [
      'attribute' => 'set_id',
      'filter' =>  ArrayHelper::map(LandingSet::getByCategory(), 'id', 'name'),
      'format' => 'raw',
      'value' => function($model) {
        return $model->landingSet ? Html::a($model->landingSet->name, [
          '/promo/landing-sets/index/', 'LandingSetSearch[id]' => $model->set_id
        ],
        ['data-pjax' => 0]) : null;
      },
      'contentOptions' => ['style'=>'min-width: 70px; overflow: auto; word-wrap: break-word;'],
    ],
    [
      'attribute' => 'addPrelandOperatorNames',
      'format' => 'raw',
      'filter' => OperatorsDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'addPrelandOperators',
        'options' => [
          'multiple' => true
        ],
        'useSelect2' => true,
      ]),
      'width' => '170px',
      'contentOptions' => ['style'=>'max-width: 170px; overflow: auto; word-wrap: break-word;'],
    ],
    [
      'attribute' => 'offPrelandOperatorNames',
      'format' => 'raw',
      'filter' => OperatorsDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'offPrelandOperators',
        'options' => [
          'multiple' => true
        ],
        'useSelect2' => true,
      ]),
      'width' => '170px',
      'contentOptions' => ['style'=>'max-width: 170px; overflow: auto; word-wrap: break-word;'],
    ],
    [
      'attribute' => 'blockedOperatorNames',
      'format' => 'raw',
      'filter' => OperatorsDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'blockedOperators',
        'options' => [
          'multiple' => true
        ],
        'useSelect2' => true,
      ]),
      'width' => '170px',
      'contentOptions' => ['style'=>'max-width: 170px; overflow: auto; word-wrap: break-word;'],
    ],
    [
      'attribute' => 'status',
      'filter' => $searchModel->getStatuses(),
      'value' => 'currentStatusName',
    ],
    [
      'attribute' => 'ads_type',
      'filter' => \mcms\promo\models\AdsType::getDropDown(),
      'value' => 'currentAdsTypeName',
    ],
    [
      'attribute' => 'created_at',
      'format' => 'datetime',
      'filter' => DatePicker::widget([
        'model' => $searchModel,
        'attribute' => 'createdFrom',
        'attribute2' => 'createdTo',
        'type' => DatePicker::TYPE_RANGE,
        'separator' => '<i class="glyphicon glyphicon-calendar"></i>',
        'language' => Yii::$app->language . '-' . strtoupper(Yii::$app->language),
        'pluginOptions' => [
          'format' => 'yyyy-mm-dd',
          'orientation' => 'bottom',
          'autoclose' => true,
        ]
      ]),

    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{update} {disable-modal} {enable-modal}',
      'visible' => function ($model) {
        return Yii::$app->user->identity->canViewUser($model->user_id);
      },
      'contentOptions'=>['class' => 'col-min-width-100'],
      'buttons'=>[
        'disable-modal' => function ($url, $model) {
          if ($model->isDeclined()) return null;
          return Modal::widget([
            'toggleButtonOptions' => [
              'tag' => 'a',
              'title' => Yii::t('yii', 'Off'),
              'label' => Html::icon('remove'),
              'class' => 'btn btn-xs btn-danger',
              'data-pjax' => 0,
            ],
            'url' => Url::to($url),
          ]);
        }
      ]
    ],
  ]
]); ?>
<?php Pjax::end(); ?>

<?php ContentViewPanel::end() ?>
<?= Html::endTag('section');?>
