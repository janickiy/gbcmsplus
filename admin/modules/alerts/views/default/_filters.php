<?php

use admin\modules\alerts\models\EventFilter;
use mcms\common\helpers\ArrayHelper;
use mcms\common\widget\AdminGridView;
use yii\widgets\Pjax;

/** @var \admin\modules\alerts\models\Event $model */
/** @var kartik\form\ActiveForm $form */
/** @var yii\data\ActiveDataProvider $filtersDataProvider */

?>

<?php Pjax::begin(['id' => 'filtersPjaxGrid']); ?>
<?= AdminGridView::widget([
    'dataProvider' => $filtersDataProvider,
    'columns' => [
        [
            'attribute' => 'type',
            'value' => function ($model) {
                return ArrayHelper::getValue(
                    EventFilter::getFilters(),
                    $model->type
                );
            }
        ],
        [
            'attribute' => 'value',
            'value' => function ($model) {
                return ArrayHelper::getValue(
                    ArrayHelper::map(EventFilter::getFilterValues($model->type), 'id', 'name'),
                    $model->value
                );

            }
        ],
        [
            'class' => 'mcms\common\grid\ActionColumn',
            'controller' => 'alerts/filter',
            'template' => '{update-modal} {delete}'
        ],
    ],
]); ?>
<?php Pjax::end(); ?>
