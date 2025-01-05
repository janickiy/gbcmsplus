<?php

use mcms\common\grid\ContentViewPanel;
use mcms\common\helpers\ArrayHelper;
use mcms\common\widget\AdminGridView;
use mcms\promo\models\Provider;
use rgk\utils\widgets\DateRangePicker;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel mcms\statistic\models\search\PostbackDataTestSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Postback Data Test';
?>

<?php ContentViewPanel::begin([
  'padding' => false,
]);
?>


<?php Pjax::begin(['id' => 'postbackDataTestPjaxGrid']); ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'columns' => [
    [
      'attribute' => 'id',
      'width' => '10px',
    ],
    [
      'attribute' => 'provider_id',
      'format' => 'raw',
      'filter' => Provider::getNotRgkProviders(),
      'value' => function ($model) {
        /* @var $model \mcms\statistic\models\PostbackDataTest */
        return $model->provider->getViewLink();
      }
    ],
    [
      'attribute' => 'requestData',
      'format' => 'html',
      'contentOptions' => ['style' => 'padding:0;border-top:0'],
      'value' => function ($model) {
        /* @var $model \mcms\statistic\models\PostbackDataTest */
        return AdminGridView::widget([
          'dataProvider' => $model->getRequestDataProvider(),
          'layout' => '{items}',
        ]);
      }
    ],
    [
      'attribute' => 'responseData',
      'format' => 'html',
      'contentOptions' => ['style' => 'padding:0;border-top:0'],
      'value' => function ($model) {
        /* @var $model \mcms\statistic\models\PostbackDataTest */
        return AdminGridView::widget([
          'dataProvider' => $model->getResponseDataProvider(),
          'layout' => '{items}',
        ]);
      }
    ],
    [
      'attribute' => 'time',
      'format' => 'datetime',
      'filter' => DateRangePicker::widget([
        'model' => $searchModel,
        'attribute' => 'dateRange',
        'align' => DateRangePicker::ALIGN_LEFT
      ])
    ],
    [
      'attribute' => 'status',
      'class' => '\kartik\grid\BooleanColumn',
      'trueLabel' => Yii::_t('statistic.postback_data_test.accepted_status'),
      'falseLabel' => Yii::_t('statistic.postback_data_test.failed_status'),
    ],
  ],
]); ?>
<?php Pjax::end(); ?>

<?php ContentViewPanel::end() ?>
