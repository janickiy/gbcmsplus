<?php

use mcms\common\grid\ActionColumn;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\Select2;
use mcms\notifications\models\UserInvitationEmail;
use mcms\notifications\models\UserInvitationEmailSent;
use yii\bootstrap\Html;
use mcms\common\widget\AdminGridView;
use mcms\notifications\models\search\UsersInvitationsEmailsSentSearch;
use rgk\utils\widgets\modal\Modal;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\Pjax;

/**
 * @var ActiveDataProvider $dataProvider
 * @var UsersInvitationsEmailsSentSearch $searchModel
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
    return $model->is_sent > 0 ? ['class' => 'success'] : [];
  },
  'columns' => [
    [
      'attribute' => 'id',
      'contentOptions' => ['style' => 'width: 80px']
    ],
    [
      'attribute' => 'invitation_email_id',
      'value' => function ($model) {
        /** @var UserInvitationEmailSent $model */
        return $model->invitationEmail ? $model->invitationEmail->getStringInfo() : '';
      },
      'filter' => UserInvitationEmail::dropdownList(),
    ],
    [
      'attribute' => 'invitation_id',
      'value' => function ($model) {
        /** @var UserInvitationEmailSent $model */
        return $model->invitation ? $model->invitation->getStringInfo() : '';
      },
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'invitation_id',
        'pluginOptions' => [
          'allowClear' => true,
          'ajax' => [
            'url' => Url::to(['/users/users-invitations/select2']),
            'dataType' => 'json',
            'data' => new JsExpression('function (params) {
                return {
                  strictSearch: 0,
                  q: params.term ? params.term : "",
                  username: params.term ? params.term : "",
                  status: 0
                };
          }')
          ],
        ],
      ]),
    ],
    'from',
    'to',
    'header',
    'is_sent:boolean',
    'attempts',
    [
      'class' => ActionColumn::class,
      'template' => '{sent-view}',
      'contentOptions' => ['class' => 'col-min-width-100'],
      'buttons' => [
        'sent-view' => function ($url, $model) {
          return Modal::widget([
            'toggleButtonOptions' => [
              'tag' => 'a',
              'label' => Html::icon('eye-open'),
              'title' => Yii::t('yii', 'View'),
              'class' => 'btn btn-xs btn-default',
              'data-pjax' => 0,

            ],
            'url' => $url,
          ]);
        }
      ],
    ],

  ],
]); ?>
<?php Pjax::end(); ?>

<?php ContentViewPanel::end() ?>

