<?php

use admin\modules\alerts\models\Event;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\modal\Modal;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel admin\modules\alerts\models\search\EmailSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<?php $this->beginBlock('actions'); ?>
<?= Modal::widget([
    'toggleButtonOptions' => [
        'tag' => 'a',
        'label' => Html::icon('plus') . ' ' . Yii::_t('alerts.emails.add'),
        'class' => 'btn btn-success',
        'data-pjax' => 0,
    ],
    'url' => Url::to(['create']),
]) ?>
<?php $this->endBlock() ?>

<?php Pjax::begin(['id' => 'emailsPjaxGrid', 'timeout' => 5000]); ?>
<?php ContentViewPanel::begin([
    'padding' => false,
    'header' => $this->title
]);
?>
<?= AdminGridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        [
            'attribute' => 'priority',
            'value' => function ($model) {
                return Event::getPriorities()[$model->priority];
            }
        ],
        'email',
        [
            'class' => 'mcms\common\grid\ActionColumn',
            'template' => '{update-modal} {delete}',
        ],
    ],
]); ?>
<?php ContentViewPanel::end() ?>
<?php Pjax::end(); ?>
