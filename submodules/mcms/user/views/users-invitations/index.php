<?php

use mcms\common\grid\ActionColumn;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AdminGridView;
use mcms\user\models\search\UsersInvitationsSearch;
use rgk\utils\widgets\modal\Modal;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\widgets\Pjax;

/**
 * @var ActiveDataProvider $dataProvider
 * @var UsersInvitationsSearch $searchModel
 */

?>

<?php $this->beginBlock('actions'); ?>
<?= Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => \yii\bootstrap\Html::icon('plus') . ' ' . Yii::_t('users.users-invitations.create'),
    'class' => 'btn btn-xs btn-success',
    'data-pjax' => 0,
  ],
  'url' => Url::to(['create-modal']),
]); ?>
<?php $this->endBlock() ?>

<?php ContentViewPanel::begin([
  'padding' => false,
]);
?>

<?php Pjax::begin(['id' => 'usersInvitationsPjaxGrid']); ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'rowOptions' => function ($model) {
    return $model->status > 0 ? ['class' => 'success'] : [];
  },
  'columns' => [
    [
      'attribute' => 'id',
      'contentOptions' => ['style' => 'width: 80px']
    ],
    'username',
    'contact',
    'hash',
    [
      'attribute' => 'status',
      'filter' => $searchModel->getStatuses(),
      'value' => 'statusName'
    ],
    [
      'attribute' => 'user_id',
      'format' => 'html',
      'value' => 'userLink',
    ],
    [
      'class' => ActionColumn::class,
      'template' => '{view-modal} {update-modal} {delete}',
      'contentOptions' => ['class' => 'col-min-width-100']
    ],

  ],
]); ?>
<?php Pjax::end(); ?>

<?php ContentViewPanel::end() ?>

