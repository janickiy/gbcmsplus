<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use rgk\utils\widgets\modal\Modal;
use yii\bootstrap\Html;
use yii\widgets\Pjax;

/** @var \mcms\holds\models\HoldProgramSearch $searchModel */
/** @var \yii\data\ActiveDataProvider $dataProvider */
?>
<?php $this->beginBlock('actions'); ?>
<?= Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => Html::icon('plus') . ' ' . Yii::_t('holds.main.create'),
    'class' => 'btn btn-success',
    'data-pjax' => 0,
  ],
  'url' => ['create-modal'],
]) ?>
<?php $this->endBlock() ?>

<?php ContentViewPanel::begin([ 'padding' => false ]); ?>

<?php Pjax::begin(['id' => 'hold-rules-grid']); ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'columns' => [
    'id',
    'name',
    'description',
    [
      'attribute' => 'is_default',
      'class' => '\kartik\grid\BooleanColumn',
      'trueLabel' => Yii::_t("app.common.Yes"),
      'falseLabel' => Yii::_t("app.common.No"),
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{update} {delete}',
      'contentOptions' => ['class' => 'col-min-width-100'],
    ],

  ],
]); ?>
<?php Pjax::end(); ?>

<?php ContentViewPanel::end() ?>
