<?php

use mcms\common\grid\ActionColumn;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\UserSelect2;
use mcms\user\models\LoginAttempt;
use mcms\user\models\search\LoginAttemptSearch;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\widgets\Pjax;

/**
 * @var ActiveDataProvider $dataProvider
 * @var LoginAttemptSearch $searchModel
 */

$failReasonsLabels = (new LoginAttempt())->failReasonLabels();

?>

<?= Html::beginTag('section', ['id' => 'widget-grid']);
ContentViewPanel::begin([
  'padding' => false,
]);
?>

<?php Pjax::begin(['id' => 'loginAttemptsPjaxGrid']); ?>

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
      'attribute' => 'user_id',
      'format' => 'html',
      'value' => 'userLink',
      'filter' => UserSelect2::widget([
          'model' => $searchModel,
          'attribute' => 'user_id',
          'initValueUserId' => $searchModel->user_id,
          'options' => [
            'placeholder' => '',
          ],
        ]
      ),
    ],
    [
      'attribute' => 'fail_reason',
      'value' => function ($model) {
        /** @var LoginAttempt $model */
        return $model->getFailReasonLabel();
      },
      'filter' => $failReasonsLabels,
    ],
    'login',
    'ip',
    [
      'attribute' => 'user_agent',
      'value' => function ($model) {
        /** @var LoginAttempt $model */
        $value = $model->user_agent;

        if (mb_strlen($value) < 50) {
          return $value;
        }

        return substr($value, 0, 50) . '...';
      }
    ],
    'created_at:datetime',
    [
      'class' => ActionColumn::class,
      'template' => '{view-modal}',
      'contentOptions' => ['class' => 'col-min-width-100']
    ],

  ],
]); ?>
<?php Pjax::end(); ?>

<?php ContentViewPanel::end() ?>
<?= Html::endTag('section');

