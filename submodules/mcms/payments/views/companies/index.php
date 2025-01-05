<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use yii\widgets\Pjax;
use mcms\common\helpers\Html;
use mcms\common\widget\modal\Modal;
use yii\helpers\Url;
use kartik\date\DatePicker;
/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var mcms\payments\models\search\CompanySearch $searchModel
 */
$this->title = Yii::_t('payments.company.title');
?>
<?php $this->beginBlock('actions'); ?>
<?= Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => Html::icon('plus') . ' ' . Yii::_t('payments.company.create'),
    'class' => 'btn btn-success',
    'data-pjax' => 0,
  ],
  'url' => Url::to(['/payments/companies/create']),
]) ?>
<?php $this->endBlock() ?>

<?php ContentViewPanel::begin([
  'padding' => false,
]);
?>

<?php Pjax::begin(['id' => 'companiesPjaxGrid']); ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'columns' => [
    'id',
    'name',
    'country',
    'city',
    'address',
    'post_code',
    'tax_code',
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
      'width' => '200px',
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{view-modal} {update-modal} {delete}',
    ],
  ]
]); ?>

<?php Pjax::end(); ?>

<?php ContentViewPanel::end() ?>
