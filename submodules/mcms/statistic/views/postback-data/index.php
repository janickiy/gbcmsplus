<?php

use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AdminGridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel mcms\statistic\models\search\PostbackDataSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Postback Data';
$this->registerJs(<<<JS
$(function () {
  $('#filter-form').on('submit', function(e) {
    $.pjax.submit(e, '#postbackDataPjaxGrid', {push: true, timeout: false});
  });  
});
JS
);
?>

<?php ContentViewPanel::begin([
  'padding' => false,
]);
?>

<?= $this->render('_search', [
  'model' => $searchModel,
]) ?>

<?php Pjax::begin(['id' => 'postbackDataPjaxGrid']); ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'columns' => [
    [
      'attribute' => 'id',
      'width' => '10px',
    ],
    'handler_code',
    [
      'attribute' => 'data',
      'format' => 'html',
      'value' => function ($model) {
        /* @var $model \mcms\statistic\models\PostbackData */
        return AdminGridView::widget([
          'dataProvider' => $model->getDataProvider(),
          'layout' => '{items}'
          ]);
      }
    ],
    //'data:ntext',
    'time:datetime',
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{view-modal}',
    ],
  ],
]); ?>
<?php Pjax::end(); ?>

<?php ContentViewPanel::end() ?>
