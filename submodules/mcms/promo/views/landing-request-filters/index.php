<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\modal\Modal;
use mcms\promo\models\LandingRequestFilter;
use yii\helpers\Html;
use kartik\date\DatePicker;
use yii\widgets\Pjax;
/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var mcms\promo\models\search\LandingRequestFiltersSearch $searchModel
 */
$this->title = LandingRequestFilter::translate('main');
?>

<?php $this->beginBlock('actions'); ?>
<?= Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => \yii\bootstrap\Html::icon('plus') . ' ' . LandingRequestFilter::translate('create'),
    'class' => 'btn btn-xs btn-success',
    'data-pjax' => 0,
  ],
  'url' => ['/promo/landing-request-filters/create-modal'],
])  ?>
<?php $this->endBlock() ?>


<?= Html::beginTag('section',['id'=>'widget-grid']);
ContentViewPanel::begin([
  'padding' => false,
]);
?>

<div class="platform-index">
  <?php Pjax::begin(['id' => 'landingRequestFiltersGrid']); ?>

  <?= AdminGridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'export' => false,
    'columns' => [
      [
        'attribute' => 'id',
        'contentOptions' => ['style' => 'width: 80px']
      ],
      [
        'attribute' => 'landing_id',
        'value' => function ($model) {
          /** @var LandingRequestFilter $model */
          return $model->landing->getViewLink();
        },
        'format' => 'html',
      ],
      [
        'attribute' => 'code',
        'value' => function ($model) {
          /** @var LandingRequestFilter $model */
          return $model->getProcessorName();
        },
        'filter' => LandingRequestFilter::getProcessorLabels(),
      ],
      [
        'attribute' => 'is_active',
        'class' => '\kartik\grid\BooleanColumn',
        'trueLabel' => LandingRequestFilter::translate("status-active"),
        'falseLabel' => LandingRequestFilter::translate("status-inactive"),
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
        'template' => '{update-modal} {delete}',
        'contentOptions' => ['class' => 'col-min-width-100'],
      ],
    ],
  ]); ?>
  <?php Pjax::end(); ?>

</div>

<?php ContentViewPanel::end() ?>
<?= Html::endTag('section');?>