<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\modal\Modal;
use yii\helpers\Html;
use mcms\promo\models\LandingPayType;
use kartik\date\DatePicker;
use yii\widgets\Pjax;
use yii\helpers\Url;
use mcms\common\helpers\ArrayHelper;
/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var mcms\promo\models\search\LandingPayTypeSearch $searchModel
 */
$this->title = LandingPayType::translate('main');
?>


<?= Html::beginTag('section',['id'=>'widget-grid']);
ContentViewPanel::begin([
  'padding' => false,
]);
?>

<div class="landing-pay-types-index">
  <?php Pjax::begin(['id' => 'LandingPayTypesGrid']); ?>

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
      'code',
      'name',
      [
        'attribute' => 'status',
        'class' => '\kartik\grid\BooleanColumn',
        'trueLabel' => LandingPayType::translate("status-active"),
        'falseLabel' => LandingPayType::translate("status-inactive"),
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
          'pluginOptions' => ['format' => 'yyyy-mm-dd', 'orientation' => 'bottom']
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