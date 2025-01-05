<?php

use mcms\common\grid\ActionColumn;
use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\modal\Modal;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\Html;
use mcms\promo\models\Country;

/** @var \mcms\promo\models\search\CountrySearch $searchModel */
/** @var \yii\data\ActiveDataProvider $dataProvider */
?>

<?php $this->beginBlock('actions'); ?>
<?= Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => \yii\bootstrap\Html::icon('plus') . ' ' . Yii::_t('promo.countries.create'),
    'class' => 'btn btn-xs btn-success',
    'data-pjax' => 0,
  ],
  'url' => Url::to(['/promo/countries/create-modal']),
]); ?>
<?php $this->endBlock() ?>

<?= Html::beginTag('section', ['id' => 'widget-grid']);
ContentViewPanel::begin([
    'padding' => false,
]);
?>

<?php Pjax::begin(['id' => 'countriesGrid']); ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'rowOptions' => function ($model) {
    return $model->status === $model::STATUS_INACTIVE ? ['class' => 'danger'] : [];
  },
  'columns' => [
    [
      'attribute' => 'id',
      'contentOptions' => ['style' => 'width: 80px']
    ],
    'name',
    'code',
    'local_currency',
    [
      'attribute' => 'status',
      'filter' => $searchModel->getStatuses(),
      'value' => 'currentStatusName'
    ],
    [
      'header' => Yii::_t('promo.operators.main'),
      'format' => 'raw',
      'value' => 'operatorsLink'
    ],
    [
      'class' => ActionColumn::class,
      'template' => '{view-modal} {update-modal} {disable} {enable}',
      'contentOptions' => ['class' => 'col-min-width-100']
    ],

  ],
]); ?>
<?php Pjax::end(); ?>

<?php ContentViewPanel::end() ?>
<?= Html::endTag('section');
