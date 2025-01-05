<?php

use mcms\common\grid\ActionColumn;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\AjaxButtons;
use mcms\common\widget\modal\Modal;
use mcms\promo\controllers\PrelandDefaultsController;
use mcms\promo\Module;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
?>

<?php Pjax::begin(['id' => 'preland-defaults-pjax']); ?>
<?php ContentViewPanel::begin([
  'padding' => false,
  'header' => Yii::_t('promo.sources.prelands_rules'),
  'buttons' => [],
  'toolbar' =>  Modal::widget([
    'toggleButtonOptions' => [
      'tag' => 'a',
      'label' => Html::icon('plus') . ' ' . PrelandDefaultsController::translate('create'),
      'class' => 'btn btn-xs btn-success',
      'data-pjax' => 0,
    ],
    'url' => Url::to(['/' . Module::getInstance()->id . '/preland-defaults/form-modal', 'source_id' => $model->id ]),
  ])
]);
?>

<?= AdminGridView::widget([
  'dataProvider' => $model->getPrelandOperators(),
  'export' => false,
  'columns' => [
    'id',
    [
      'attribute' => 'type',
      'value' => 'currentTypeName'
    ],
    [
      'attribute' => 'user_id',
      'format' => 'raw',
      'value' => function ($model) {
        return $model->userLink ?: PrelandDefaultsController::translate('empty_user');
      },
    ],
    [
      'attribute' => 'source_id',
      'format' => 'raw',
      'value' => 'sourceLink',
    ],
    [
      'attribute' => 'operators',
      'format' => 'raw',
      'value' => function ($model) {
        /** @var $model \mcms\promo\models\PrelandDefaults */
        return $model->getOperatorNames();
      },
    ],
    [
      'class' => ActionColumn::class,
      'template' => '{form-modal} {delete} {enable} {disable}',
      'visible' => Yii::$app->user->identity->canViewUser($model->user_id),
      'controller' => 'promo/preland-defaults',
      'contentOptions' => ['class' => 'col-min-width-100'],
      'buttons' => [
        'delete' =>  function ($url, $model, $key) {
          if (!$model->source_id) return false;
          $options = [
            'title' => Yii::t('yii', 'Delete'),
            'aria-label' => Yii::t('yii', 'Delete'),
            AjaxButtons::CONFIRM_ATTRIBUTE => Yii::t('yii', 'Are you sure you want to delete this item?'),
            AjaxButtons::AJAX_ATTRIBUTE => 1,
            'data-pjax' => 0,
            'class' => 'btn btn-xs btn-default'
          ];
          return Html::a(Html::icon('trash'), $url, $options);
        },
        'disable' => function ($url, $model) {
          if (!$model->source_id) return false;
          if (!method_exists($model, 'isDisabled')) return null;
          if ($model->isDisabled()) return null;
          $options = [
            'title' => Yii::t('yii', 'Off'),
            'aria-label' => Yii::t('yii', 'Off'),
            'data-pjax' => '0',
            'class' => 'btn btn-xs btn-danger',
            AjaxButtons::AJAX_ATTRIBUTE => 1
          ];
          return Html::a(Html::icon('remove'), $url, $options);
        },
        'enable' => function ($url, $model) {
          if (!$model->source_id) return false;
          if (!method_exists($model, 'isDisabled')) return null;
          if (!$model->isDisabled()) return null;
          $options = [
            'title' => Yii::t('yii', 'On'),
            'aria-label' => Yii::t('yii', 'On'),
            'data-pjax' => '0',
            'class' => 'btn btn-xs btn-success',
            AjaxButtons::AJAX_ATTRIBUTE => 1
          ];
          return Html::a(Html::icon('ok'), $url, $options);
        },
      ],
    ],
  ],
]); ?>

<?php ContentViewPanel::end() ?>
<?php Pjax::end() ?>