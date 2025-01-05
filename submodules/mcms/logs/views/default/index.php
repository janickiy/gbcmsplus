<?php

use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\Select2;
use yii\widgets\Pjax;

$this->title = Yii::_t('logs.main.logs');
?>


<?php ContentViewPanel::begin([
  'padding' => false,
]);
?>
<?php //Pjax::begin() ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'columns' => [
    [
      'attribute' => 'label',
      'class' => '\mcms\logs\components\grid\Label',
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'label',
        'data' => $searchModel->getFilterLabels(),
        'options' => [
          'placeholder' => '',
          'multiple' => true
        ],
      ]),
      'contentOptions' => ['style' => 'width: 520px;']
    ],
    [
      'attribute' => 'EventTime',
      'format' => ['datetime'],
      'contentOptions' => ['style' => 'width: 320px;'],
      'filter' => \kartik\daterange\DateRangePicker::widget([
        'model'=>$searchModel,
        'attribute'=>'EventTimeRange',
        'language'=>Yii::$app->language,
        'convertFormat'=>true,
        'pluginOptions'=>[
          'timePicker'=>false,
          'locale'=>[
            'format'=>'Y-m-d'
          ]
        ]
      ])
    ],
    [
      'attribute' => 'EventData',
      'format' => "raw",
      'value' => function($model){
       return mcms\common\widget\modal\Modal::widget([
          'viewPath' => '@mcms/logs/views/default/_view_modal',
          'viewParams' => ['model'=>$model],
          'single' => true,
          'size' => \mcms\common\widget\modal\Modal::SIZE_LG,
          'toggleButtonOptions' => [
            'label' => \yii\bootstrap\Html::icon('eye-open'),
            'title' => Yii::t('yii', 'View'),
            'class' => 'btn btn-xs btn-default',
            'data-pjax' => 0,
            'url' => false,
          ],
        ]);
      },
      'contentOptions' => ['style' => 'width: 120px;']
    ]
  ],
]); ?>
<?php //Pjax::end() ?>

<?php ContentViewPanel::end();