<?php

use mcms\common\grid\ActionColumn;
use mcms\common\grid\ContentViewPanel;
use yii\bootstrap\Html;
use mcms\common\widget\AdminGridView;
use mcms\notifications\models\search\UsersInvitationsEmailsSearch;
use rgk\utils\widgets\modal\Modal;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\widgets\Pjax;

/**
 * @var ActiveDataProvider $dataProvider
 * @var UsersInvitationsEmailsSearch $searchModel
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
  'size' => Modal::SIZE_LG,
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
    return $model->is_complete > 0 ? ['class' => 'success'] : [];
  },
  'columns' => [
    [
      'attribute' => 'id',
      'contentOptions' => ['style' => 'width: 80px']
    ],
    'from',
    'header',
    'is_complete:boolean',
    [
      'class' => ActionColumn::class,
      'template' => '{update-modal} {delete}',
      'contentOptions' => ['class' => 'col-min-width-100'],
      'buttons' => [
        'update-modal' => function ($url, $model) {
          return Modal::widget([
            'size' => Modal::SIZE_LG,
            'toggleButtonOptions' => [
              'tag' => 'a',
              'label' => Html::icon('pencil'),
              'title' => Yii::t('yii', 'Update'),
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

<?php ContentViewPanel::end() ?>

