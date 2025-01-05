<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\modal\Modal;
use mcms\common\widget\Select2;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;
use mcms\common\widget\UserSelect2;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\controllers\PrelandDefaultsController;
use yii\helpers\Url;
use mcms\promo\Module;
use yii\bootstrap\Html;
use mcms\common\grid\ActionColumn;

/* @var $this yii\web\View */
/* @var $searchModel mcms\promo\models\search\PrelandDefaultsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/** @var \mcms\user\Module $userModule */
$userModule = Yii::$app->getModule('users');
?>
<?php $this->beginBlock('actions'); ?>
<?= $this->render('_create_button', ['userId' => null]) ?>
<?php $this->endBlock() ?>

<?= Html::beginTag('section',['id'=>'widget-grid']);
ContentViewPanel::begin([
  'padding' => false,
]);
?>

  <div class="preland-defaults-index">

    <?php Pjax::begin(['id' => 'preland-defaults-pjax']); ?>
    <?= AdminGridView::widget([
      'dataProvider' => $dataProvider,
      'filterModel' => $searchModel,
      'export' => false,
      'rowOptions' => function ($model) {
        return ['class' => ArrayHelper::getValue($model::getStatusColors(), $model->status, '')];
      },
      'columns' => [
        'id',
        [
          'attribute' => 'type',
          'filter' => $searchModel->getTypes(),
          'value' => 'currentTypeName'
        ],
        [
          'attribute' => 'user_id',
          'filter' => UserSelect2::widget([
            'roles' => $userModule::PARTNER_ROLE,
            'model' => $searchModel,
            'options' => [
              'placeholder' => '',
            ],
            'attribute' => 'user_id',
            'initValueUserId' => $searchModel->user_id
          ]),
          'format' => 'raw',
          'value' => function ($model) {
            return $model->userLink ?: PrelandDefaultsController::translate('empty_user');
          },
        ],
        [
          'attribute' => 'stream_id',
          'format' => 'raw',
          'value' => 'streamLink',
          'filter' => Select2::widget([
            'model' => $searchModel,
            'attribute' => 'stream_id',
            'data' => $streamsData,
            'options' => [
              'placeholder' => '',
            ],
            'pluginOptions' => ['allowClear' => true],
          ])
        ],
        [
          'attribute' => 'source_id',
          'format' => 'raw',
          'value' => 'sourceLink',
          'filter' => Select2::widget([
            'model' => $searchModel,
            'attribute' => 'source_id',
            'data' => $sourcesData,
            'options' => [
              'placeholder' => '',
            ],
            'pluginOptions' => ['allowClear' => true],
          ])
        ],
        [
          'attribute' => 'operators',
          'format' => 'raw',
          'value' => function ($model) {
            /** @var $model \mcms\promo\models\PrelandDefaults */
            return $model->getOperatorNames();
          },
          'filter' => OperatorsDropdown::widget([
            'model' => $searchModel,
            'attribute' => 'operators',
            'options' => ['prompt' => '',],
            'pluginOptions' => ['allowClear' => true],
            'useSelect2' => true
          ]),
        ],
        [
          'class' => ActionColumn::class,
          'template' => '{form-modal} {delete} {enable} {disable}',
          'visible' => Yii::$app->user->identity->canViewUser($searchModel->user_id),
          'controller' => 'promo/preland-defaults',
          'contentOptions' => ['class' => 'col-min-width-100'],
          'buttons' => [
            'form-modal' => function ($url, $model) {
              return Modal::widget([
                'toggleButtonOptions' => [
                  'tag' => 'a',
                  'title' => Yii::t('yii', 'Update'),
                  'label' => Html::icon('pencil'),
                  'class' => 'btn btn-xs btn-default',
                  'data-pjax' => 0,
                ],
                'url' => Url::to(['/' . Module::getInstance()->id . '/preland-defaults/form-modal', 'id' => $model->id]),
              ]);
            }
          ],
        ],
      ],
    ]); ?>
    <?php Pjax::end(); ?></div>

<?php ContentViewPanel::end() ?>
<?= Html::endTag('section');?>