<?php

use kartik\date\DatePicker;
use mcms\common\grid\ActionColumn;
use kartik\helpers\Html;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\UserSelect2;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\components\widgets\ProvidersDropdown;
use rgk\utils\widgets\DateRangePicker;
use rgk\utils\widgets\modal\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use mcms\promo\models\TrafficBlock;
use mcms\promo\components\widgets\BlackListTrafficBlockSwitcher;

/* @var $this yii\web\View */
/* @var $searchModel mcms\promo\models\search\TrafficBlockSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $showAddButton bool */
?>

<?= BlackListTrafficBlockSwitcher::widget(['userId' => $searchModel->user_id]) ?>

<?php ContentViewPanel::begin([
  'padding' => false,
  'toolbar' => $showAddButton
    ? Modal::widget([
      'toggleButtonOptions' => [
        'tag' => 'a',
        'label' => Html::icon('plus') . ' ' . Yii::_t('promo.traffic_block.create'),
        'class' => 'btn btn-xs btn-success',
        'data-pjax' => 0,
      ],
      'url' => ['/promo/traffic-block/create-modal/', 'userId' => $userId],
    ])
    : null,
]); ?>
<?php Pjax::begin(['id' => 'TrafficBlockGrid']); ?>

  <?= AdminGridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
      [
        'attribute' => 'id',
        'width' => '10px'
      ],
      [
        'attribute' => 'user_id',
        'format' => 'raw',
        'filter' => UserSelect2::widget([
          'model' => $searchModel,
          'attribute' => 'user_id',
          'initValueUserId' => $searchModel->user_id,
          'options' => [
            'placeholder' => '',
          ],
        ]),
        'value' => 'userLink',
        'visible' => !$userId,
      ],
      [
        'attribute' => 'provider_id',
        'format' => 'raw',
        'filter' => ProvidersDropdown::widget([
          'model' => $searchModel,
          'attribute' => 'provider_id',
          'options' => [
            'placeholder' => '',
          ],
          'pluginOptions' => ['allowClear' => true],
          'useSelect2' => true
        ]),
        'value' => 'providerLink',
      ],
      [
        'attribute' => 'operator_id',
        'format' => 'raw',
        'filter' => OperatorsDropdown::widget([
          'model' => $searchModel,
          'attribute' => 'operator_id',
          'options' => [
            'placeholder' => '',
          ],
          'pluginOptions' => ['allowClear' => true],
          'useSelect2' => true
        ]),
        'value' => 'operatorLink',
      ],
      [
        'attribute' => 'is_blacklist',
        'filter' => TrafficBlock::getIsBlacklistOptions(),
        'contentOptions' => function (TrafficBlock $model) {
          return ['class' => $model->is_blacklist ? 'text-danger' : 'text-success'];
        },
        'value' => function (TrafficBlock $model) {
          $translate = $model->is_blacklist ? 'is_blacklist_true' : 'is_blacklist_false';
          return ucfirst(TrafficBlock::t($translate));
        },
        'width' => '80px',
      ],
      [
        'attribute' => 'created_at',
        'format' => 'datetime',
        'filter' => DatePicker::widget([
          'model' => $searchModel,
          'attribute' => 'createdFrom',
          'attribute2' => 'createdTo',
          'type' => DatePicker::TYPE_RANGE,
          'separator' => '<i class="glyphicon glyphicon-calendar kv-dp-icon"></i>',
          'pluginOptions' => ['format' => 'yyyy-mm-dd', 'autoclose' => true, 'orientation' => 'bottom']
        ]),
        'width' => '120px',
      ],
      [
        'attribute' => 'updated_at',
        'format' => 'datetime',
        'filter' => DatePicker::widget([
          'model' => $searchModel,
          'attribute' => 'updatedFrom',
          'attribute2' => 'updatedTo',
          'type' => DatePicker::TYPE_RANGE,
          'separator' => '<i class="glyphicon glyphicon-calendar kv-dp-icon"></i>',
          'pluginOptions' => ['format' => 'yyyy-mm-dd', 'autoclose' => true, 'orientation' => 'bottom']
        ]),
        'width' => '120px',
      ],
      'attribute' => 'comment',
      [
        'class' => ActionColumn::class,
        'controller' => 'promo/traffic-block',
        'template' => '{update-modal} {delete}',
        'contentOptions' => ['class' => 'col-min-width-100'],
        'urlCreator' => function ($action, $model) use ($userId) {
          return $action === 'update-modal'
            ? ['/promo/traffic-block/update-modal', 'id' => $model['id'], 'userId' => $userId]
            : Url::to(['/promo/traffic-block/' . $action, 'id' => $model['id']]);
        },
      ],
    ],
  ]); ?>

<?php Pjax::end(); ?>
<?php ContentViewPanel::end();

