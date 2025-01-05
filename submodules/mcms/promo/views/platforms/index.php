<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\modal\Modal;
use yii\helpers\Html;
use mcms\promo\models\Platform;
use kartik\date\DatePicker;
use yii\widgets\Pjax;
use yii\helpers\Url;
use mcms\common\helpers\ArrayHelper;
/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var mcms\promo\models\search\PlatformSearch $searchModel
 */
$this->title = Platform::translate('main');
?>

<?php $this->beginBlock('actions'); ?>
<?= Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => \yii\bootstrap\Html::icon('plus') . ' ' . Platform::translate('create'),
    'class' => 'btn btn-xs btn-success',
    'data-pjax' => 0,
  ],
  'url' => ['/promo/platforms/create-modal'],
])  ?>
<?php $this->endBlock() ?>


<?= Html::beginTag('section',['id'=>'widget-grid']);
ContentViewPanel::begin([
  'padding' => false,
]);
?>

<div class="platform-index">
  <?php Pjax::begin(['id' => 'platformsGrid']); ?>

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
      'match_string',
      [
        'attribute' => 'status',
        'class' => '\kartik\grid\BooleanColumn',
        'trueLabel' => Platform::translate("status-active"),
        'falseLabel' => Platform::translate("status-inactive"),
        'filterWidgetOptions' => [
          'pluginOptions' => [
            'allowClear' => true,
            'width' => '155px',
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
        'template' => '{update-modal} {enable} {disable}',
        'contentOptions' => ['class' => 'col-min-width-100'],
      ],
    ],
  ]); ?>
  <?php Pjax::end(); ?>

</div>

<?php ContentViewPanel::end() ?>
<?= Html::endTag('section');?>