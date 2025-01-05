<?php

use kartik\widgets\DatePicker;
use mcms\common\grid\ContentViewPanel;
use rgk\export\ExportMenu;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\UserSelect2;
use mcms\statistic\models\Postback;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel mcms\statistic\models\search\PostbackSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/** @var string $exportWidgetId */

$columns = [
  'id',
  'userId' => [
    'attribute' => 'userId',
    'format' => 'raw',
    'enableSorting' => false,
    'value' => function ($model) {
      return $model->user ? $model->user->viewLink : null;
    }
  ],
  'transId',
  'hit_id',
  [
    'attribute' => 'type',
    'filter' => Postback::getTypesList(),
    'value' => function ($model) {
      return $model->typeLabel;
    }
  ],
  [
    'attribute' => 'status',
    'filter' => Postback::getStatusList(),
    'value' => function ($model) {
      return $model->statusLabel;
    }
  ],
  'errors',
  [
    'attribute' => 'status_code',
    'contentOptions' => ['style' => 'min-width: 100px']
  ],
  'url',
  [
    'attribute' => 'data',
    'format' => 'raw',
    'value' => function ($model) {
      return $model->parsedData;
    }
  ],
  'datetime' => [
    'attribute' => 'time',
    'format' => 'datetime',
  ]
];

$toolbar = ExportMenu::widget([
  'id' => $exportWidgetId,
  'dataProvider' => $dataProvider,
  'dropdownOptions' => ['class' => 'btn-xs btn-success', 'menuOptions' => ['class' => 'pull-right']],
  'template' => '{menu}',
  'columns' => $columns,
  'target' => ExportMenu::TARGET_BLANK,
  'pjaxContainerId' => 'usersPjaxGrid',
  'filename' => Yii::_t('main.postbacks'),
  'exportConfig' => [
    ExportMenu::FORMAT_HTML => false,
    ExportMenu::FORMAT_PDF => false,
    ExportMenu::FORMAT_EXCEL => false,
  ],
]);

?>

<?php ContentViewPanel::begin([
  'padding' => false,
  'toolbar' => $toolbar,
]);
?>

<?php Pjax::begin(['id' => 'statistic-pjax']); ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'columns' => ArrayHelper::merge($columns, [
    // TRICKY приходится строки с фильтрациями типа select2, datepicker мержить таким образом,
    // иначе не работает переинициализация этих виджетов после pjax обновления.
    // Можно решить если pjax начать до ExportMenu, но тогда при фулскрине грида фильтрация сворачивает фулскрин.
    // поэтому ничего лучше текущего варианта не придумали
    'userId' => [
      'filter' => UserSelect2::widget([
        'model' => $searchModel,
        'attribute' => 'userId',
        'initValueUserId' => $searchModel->userId,
        'options' => [
          'placeholder' => '',
        ],
      ]),
    ],
    'datetime' => [
      'filter' => DatePicker::widget([
        'model' => $searchModel,
        'attribute' => 'createdFrom',
        'attribute2' => 'createdTo',
        'type' => DatePicker::TYPE_RANGE,
        'separator' => '<i class="glyphicon glyphicon-calendar"></i>',
        'pluginOptions' => [
          'format' => 'yyyy-mm-dd',
          'orientation' => 'bottom',
          'autoclose' => true,
        ]
      ]),
    ]
  ]),
]); ?>
<?php Pjax::end(); ?>
<?php ContentViewPanel::end();