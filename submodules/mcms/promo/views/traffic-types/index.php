<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\modal\Modal;
use yii\helpers\Html;
use mcms\promo\models\TrafficType;
use kartik\date\DatePicker;
use yii\widgets\Pjax;
use yii\helpers\Url;
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Link;
use mcms\common\widget\Select2;
/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var mcms\promo\models\search\TrafficTypeSearch $searchModel
 */
$this->title = TrafficType::translate('main');
?>

<?= Html::beginTag('section',['id'=>'widget-grid']);
ContentViewPanel::begin([
  'padding' => false,
]);
?>
<div class="traffic-types-index">
  <?php
  $pjaxId = 'trafficTypesGrid';
  Pjax::begin(['id' => $pjaxId]); ?>

  <?= AdminGridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'export' => false,
    'rowOptions' => function ($model) {
      return ['class' => ArrayHelper::getValue($model::getStatusColors(), $model->status, '')];
    },
    'columns' => [
      [
        'attribute' => 'id',
        'contentOptions' => ['style' => 'width: 80px']
      ],
      'name',
      [
        'attribute' => 'status',
        'class' => '\kartik\grid\BooleanColumn',
        'trueLabel' => TrafficType::translate("status-active"),
        'falseLabel' => TrafficType::translate("status-inactive"),
        'filterWidgetOptions' => [
          'pluginOptions' => [
            'allowClear' => true
          ],
          'options' => [
            'placeholder' => '',
          ],
        ],
      ],
      [
        'attribute' => 'created_at',
        'format' => 'datetime',
        'filter' => DatePicker::widget([
          'model' => $searchModel,
          'attribute' => 'createdFrom',
          'attribute2' => 'createdTo',
          'type' => DatePicker::TYPE_RANGE,
          'separator' => '<i class="glyphicon glyphicon-calendar"></i>',
          'pluginOptions' => ['format' => 'yyyy-mm-dd', 'autoclose' => true, 'orientation' => 'bottom']
        ]),
      ],
      [
        'class' => 'mcms\common\grid\ActionColumn',
        'template' => '{update} {enable} {disable}',
        'contentOptions' => ['class' => 'col-min-width-100'],
        'buttons' => [
          'update' => function ($url, $model) use ($pjaxId) {
            return Modal::widget([
              'options' => ['id' => $pjaxId . '-' . $model->id],
              'toggleButtonOptions' => [
                'tag' => 'a',
                'label' => \yii\bootstrap\Html::icon('pencil'),
                'class' => 'btn btn-xs btn-default',
                'data-pjax' => 0,
              ],
              'url' => $url,
            ]);
          },
        ],
      ],
    ],
  ]); ?>
  <?php Pjax::end(); ?>

</div>

<?php ContentViewPanel::end() ?>
<?= Html::endTag('section');?>