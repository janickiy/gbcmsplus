<?php
use mcms\common\widget\modal\Modal;
use mcms\common\widget\Select2;
use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\pages\models\FaqCategory;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel mcms\pages\models\FaqSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->blocks['actions'] = Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => \yii\bootstrap\Html::icon('plus') . ' ' . Yii::_t('faq.create_faq'),
    'class' => 'btn btn-success',
    'data-pjax' => 0,
  ],
  'url' => ['/pages/faq/create'],
]);
?>

<?php ContentViewPanel::begin([
  'padding' => false,
]);
?>

<?php Pjax::begin(['id' => 'pages-pjax']); ?>
<?= AdminGridView::widget([
  'id' => 'pages-grid',
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'columns' => [
    'id',
    'question',
    'sort',
    [
      'attribute' => 'faq_category_id',
      'value' => function($model) {
        return $model->faqCategory->name;
      },
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'faq_category_id',
        'data' => FaqCategory::getAllCategoriesDropDownArray(),
        'options' => [
          'placeholder' => '',
        ],
        'pluginOptions' => [
          'allowClear' => true,
        ]
      ]),
    ],
    [
      'attribute' => 'visible',
      'class' => '\kartik\grid\BooleanColumn',
      'trueLabel' => Yii::_t("pages.faq.yes"),
      'falseLabel' => Yii::_t("pages.faq.no"),
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{update} {view} {delete}',
      'contentOptions' => ['class' => 'col-min-width-100'],
      'buttons' => [
        'view' => function($url) {
          return Modal::widget([
            'toggleButtonOptions' => [
              'tag' => 'a',
              'label' => \yii\bootstrap\Html::icon('eye-open'),
              'title' => Yii::t('yii', 'View'),
              'class' => 'btn btn-xs btn-default',
              'data-pjax' => 0,
            ],
            'url' => $url,
          ]);
        },
        'update' => function($url) {
          return Modal::widget([
            'toggleButtonOptions' => [
              'tag' => 'a',
              'label' => \yii\bootstrap\Html::icon('pencil'),
              'title' => Yii::t('yii', 'Update'),
              'class' => 'btn btn-xs btn-default',
              'data-pjax' => 0,
            ],
            'url' => $url,
          ]);
        },
      ],
    ],
  ],
]); ?>
<?php Pjax::end(); ?>
<?php ContentViewPanel::end() ?>
