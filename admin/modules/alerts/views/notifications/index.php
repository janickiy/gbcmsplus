<?php

use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AdminGridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel admin\modules\alerts\models\search\NotificationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>

<?php Pjax::begin(['id' => 'notificationsPjaxGrid']); ?>
<?php ContentViewPanel::begin([
    'padding' => false,
    'header' => $this->title
]);
?>
<?= AdminGridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        'id',
        'message',
    ],
]); ?>
<?php ContentViewPanel::end() ?>
<?php Pjax::end(); ?>
