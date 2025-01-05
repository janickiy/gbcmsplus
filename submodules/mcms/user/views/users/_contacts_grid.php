<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ActionColumn;
use mcms\common\grid\ContentViewPanel;
use mcms\user\models\search\UserContactsSearch;
use mcms\user\models\User;
use mcms\user\models\UserContact;
use rgk\utils\widgets\modal\Modal;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\helpers\Url;

/**
 * @var ActiveDataProvider $dataProvider
 * @var UserContactsSearch $searchModel
 * @var User $user
 */


?>

<?php ContentViewPanel::begin([
  'padding' => false,
  'toolbar' => Modal::widget([
    'toggleButtonOptions' => [
      'tag' => 'a',
      'label' => \yii\bootstrap\Html::icon('plus') . ' ' . Yii::_t('users.forms.user_contacts_create'),
      'class' => 'btn btn-xs btn-success',
      'data-pjax' => 0,
    ],
    'url' => Url::to(['user-contacts/create-modal', 'id' => $user->id]),
  ]),
]);
?>

<?php Pjax::begin(['id' => 'userContactsPjaxGrid']); ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'columns' => [
    [
      'attribute' => 'type',
      'value' => function ($model) {
        /** @var UserContact $model */
        return $model->getTypeLabel();
      },
      'filter' => UserContact::getTypes(),
    ],
    [
      'attribute' => 'data',
      'format' => 'raw',
      'value' => function ($model) {
        /** @var UserContact $model */

        return $model->type ? Html::a($model->data, $model->getBuiltData(), ['target' => '_blank']) : $model->data;
      },
    ],
    [
      'attribute' => 'is_deleted',
      'class' => '\kartik\grid\BooleanColumn',
      'trueLabel' => Yii::_t('app.common.on'),
      'falseLabel' => Yii::_t('app.common.off'),
      'filterWidgetOptions' => [
        'pluginOptions' => [
          'allowClear' => true
        ],
        'options' => [
          'placeholder' => '',
        ],
      ],
      'value' => function ($model) {
        /** @var UserContact $model */
        return !$model->is_deleted;
      }
//      'filter' => [
//        Yii::_t('app.common.on'),
//        Yii::_t('app.common.off'),
//      ],
    ],
    'created_at:datetime',
    'updated_at:datetime',
    [
      'class' => ActionColumn::class,
      'controller' => 'users/user-contacts',
      'template' => '{update-modal} {delete}',
      'contentOptions' => ['class' => ' col-min-width-100'],
    ],
  ],
]); ?>
<?php Pjax::end(); ?>


<?php ContentViewPanel::end() ?>
