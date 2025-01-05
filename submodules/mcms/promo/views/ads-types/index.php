<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\modal\Modal;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\helpers\Url;
use mcms\promo\models\AdsType;
use mcms\common\widget\Select2;

$id = 'ads-types';

/* @var $this \mcms\common\web\View */
/* @var $searchModel mcms\promo\models\search\AdsTypeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>

<?= Html::beginTag('section',['id'=>'widget-grid']);
ContentViewPanel::begin([
  'padding' => false,
]);
?>

<?php Pjax::begin(['id' => $id . 'PjaxGrid']); ?>
<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'rowOptions' => function ($model) {
    return $model->status === $model::STATUS_INACTIVE ? ['class' => 'danger'] : [];
  },
  'columns' => [
    'id',
    'code',
    'name',
    'description:raw',
    [
      'attribute' => 'is_default',
      'class' => '\kartik\grid\BooleanColumn',
      'trueLabel' => Yii::_t("app.common.Yes"),
      'falseLabel' => Yii::_t("app.common.No"),
    ],
    [
      'attribute' => 'status',
      'filter' => $searchModel->getStatuses(),
      'value' => function ($model) {
        return $model->getStatuses($model->status);
      },
      'contentOptions' => ['style' => 'min-width: 120px']
    ],
    [
      'attribute' => 'security',
      'filter' => $searchModel::getAvailableSecurity(),
    ],
    [
      'attribute' => 'profit',
      'filter' => $searchModel::getAvailableProfit(),
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{update-modal} {disable} {enable}',
      'contentOptions' => ['class' => 'col-min-width-100'],
    ],
  ],
]); ?>
<?php Pjax::end(); ?>
<?php ContentViewPanel::end() ?>
<?= Html::endTag('section');?>