<?php
/** @var \mcms\promo\models\search\LandingSetSearch $searchModel */
/** @var \yii\data\ActiveDataProvider $dataProvider */
use kartik\date\DatePicker;
use mcms\common\grid\ContentViewPanel;
use mcms\common\helpers\Html;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\modal\Modal;
use mcms\common\widget\Select2;
use mcms\promo\models\LandingCategory;
use yii\bootstrap\Html as BHtml;
use yii\helpers\Url;
use yii\widgets\Pjax;

?>
<?php $this->beginBlock('actions'); ?>
<?= Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => BHtml::icon('plus') . ' ' . Yii::_t('promo.landing_sets.create'),
    'class' => 'btn btn-success',
    'data-pjax' => 0,
  ],
  'url' => ['create-modal'],
])  ?>
<?php $this->endBlock() ?>

<?php ContentViewPanel::begin(['padding' => false]) ?>
<?php Pjax::begin(['id' => 'landing-sets-list']) ?>
<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'columns' => [
    [
      'attribute' => 'id',
      'contentOptions' => ['style' => 'width: 50px'],
    ],
    [
      'attribute' => 'name',
      'contentOptions' => ['style' => 'width: 230px'],
    ],
    [
      'attribute' => 'category.name',
      'label' => Yii::_t('promo.landings.attribute-category_id'),
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'category_id',
        'data' => LandingCategory::getAllMap(),
        'options' => [
          'placeholder' => '',
        ],
        'pluginOptions' => [
          'allowClear' => true,
        ]
      ]),
      'format' => 'raw',
      'value' => 'categoryLink',
      'width' => '120px'
    ],
    [
      'attribute' => 'autoupdate',
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
      'header' => Yii::_t('promo.landing_sets.landings'),
      'format' => 'raw',
      'value' => 'itemsLabel',
      'contentOptions' => ['style' => 'width: 100px']
    ],
    [
      'header' => Yii::_t('promo.landing_sets.webmaster-sources'),
      'format' => 'raw',
      'value' => 'sourcesLink'
    ],
    [
      'attribute' => 'created_at',
      'format' =>  'datetime',
      'filter' => DatePicker::widget([
        'model' => $searchModel,
        'attribute' => 'createdFrom',
        'attribute2' => 'createdTo',
        'type' => DatePicker::TYPE_RANGE,
        'separator' => '<i class="glyphicon glyphicon-calendar"></i>',
        'pluginOptions' => [
          'format' => 'yyyy-mm-dd',
          'orientation' => 'bottom',
          'autoclose' => true,
        ]
      ]),
      'contentOptions' => ['style' => 'width: 130px;']
    ],
    [
      'attribute' => 'updated_at',
      'format' =>  'datetime',
      'filter' => DatePicker::widget([
        'model' => $searchModel,
        'attribute' => 'updatedFrom',
        'attribute2' => 'updatedTo',
        'type' => DatePicker::TYPE_RANGE,
        'separator' => '<i class="glyphicon glyphicon-calendar"></i>',
        'pluginOptions' => [
          'format' => 'yyyy-mm-dd',
          'orientation' => 'bottom',
          'autoclose' => true,
        ]
      ]),
      'contentOptions' => ['style' => 'width: 130px;']
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{update} {delete}',
      'contentOptions' => ['class' => 'col-min-width-100'],
    ],
  ],
]); ?>
<?php Pjax::end() ?>
<?php ContentViewPanel::end() ?>