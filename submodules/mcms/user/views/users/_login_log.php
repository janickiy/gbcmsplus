<?php
use mcms\user\models\User;
use yii\bootstrap\Modal;
use kartik\grid\GridView;
use yii\widgets\Pjax;

/* @var $model User */
?>

<?php if ($model->user->id) : ?>
  <?php Modal::begin([
    'id' => 'login_log',
    'header' => '<h4 class="modal-title">' . Yii::_t('login_logs.log') . '</h4>',
  ]) ?>

  <?php Pjax::begin(['clientOptions' => ['container' => 'pjax-container']]); ?>

  <?= GridView::widget([
    'dataProvider' => $model->user->getLoginLog(),
    'krajeeDialogSettings' => ['overrideYiiConfirm' => false],
    'export' => false,
    'layout' => '{items}',
    'columns' => [
      'created_at:datetime',
      'user_agent',
      'ip',
    ]
  ]); ?>

  <?php Pjax::end(); ?>
  <?php Modal::end() ?>
<?php endif;