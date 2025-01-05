<?php

use admin\modules\alerts\models\Event;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\modal\Modal;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel admin\modules\alerts\models\search\EventSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<?php $this->beginBlock('actions'); ?>
<?= Modal::widget([
    'toggleButtonOptions' => [
        'tag' => 'a',
        'label' => Html::icon('plus') . ' ' . Yii::_t('alerts.main.add'),
        'class' => 'btn btn-success',
        'data-pjax' => 0,
    ],
    'url' => Url::to(['create']),
]) ?>
<?php $this->endBlock() ?>

<?php ContentViewPanel::begin([
    'padding' => false
]);
?>
<?php Pjax::begin(['id' => 'eventsPjaxGrid']); ?>
<?= AdminGridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        'id',
        'name',
        [
            'attribute' => 'metric',
            'value' => function ($model) {
                return Event::getMetrics()[$model->metric];
            },
            'filter' => Event::getMetrics()
        ],
        [
            'attribute' => 'priority',
            'value' => function ($model) {
                return Event::getPriorities()[$model->priority];
            },
            'filter' => Event::getPriorities()
        ],
        [
            'attribute' => 'is_active',
            'class' => '\kartik\grid\BooleanColumn',
            'trueLabel' => Yii::_t('app.common.Yes'),
            'falseLabel' => Yii::_t('app.common.No'),
        ],
        [
            'class' => 'mcms\common\grid\ActionColumn',
            'template' => '{update} {delete} {disable} {enable}',
        ],
    ],
]); ?>
<?php Pjax::end(); ?>
<?php ContentViewPanel::end();
