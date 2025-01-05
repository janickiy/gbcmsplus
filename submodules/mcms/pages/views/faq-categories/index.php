<?php
use mcms\common\grid\ContentViewPanel;
use mcms\common\helpers\Link;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\modal\Modal;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel mcms\pages\models\FaqCategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->blocks['actions'] = Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => \yii\bootstrap\Html::icon('plus') . ' ' . Yii::_t('faq.create_category'),
    'class' => 'btn btn-success',
    'data-pjax' => 0,
  ],
  'url' => ['/pages/faq-categories/create'],
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
    'name',
    'sort',
    [
      'attribute' => 'visible',
      'class' => '\kartik\grid\BooleanColumn',
      'trueLabel' => Yii::_t("pages.faq.yes"),
      'falseLabel' => Yii::_t("pages.faq.no"),
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{update} {delete}',
      'contentOptions' => ['class' => 'col-min-width-100'],
      'buttons' => [
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
