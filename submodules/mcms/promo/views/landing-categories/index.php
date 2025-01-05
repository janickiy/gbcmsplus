<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\helpers\Html;
use mcms\common\widget\modal\Modal;
use rgk\theme\smartadmin\widgets\grid\BooleanColumn;
use yii\widgets\Pjax;
use yii\helpers\Url;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\assets\LandingCategoryAsset;

$id = 'landing-categories';

LandingCategoryAsset::register($this);

/**
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var \yii\db\ActiveRecord $searchModel
 */
?>

<?php $this->beginBlock('actions'); ?>
<?php $this->beginBlockAccessVerifier('create-modal', ['PromoLandingCategoriesCreateModal']) ?>
<?= Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => \yii\bootstrap\Html::icon('plus') . ' ' . Yii::_t('promo.landing_categories.create'),
    'class' => 'btn btn-xs btn-success',
    'data-pjax' => 0,
  ],
  'url' => Url::to(['/promo/' . $id . '/create-modal']),
]); ?>
<?php $this->endBlockAccessVerifier() ?>
<?php $this->endBlock() ?>


<?= Html::beginTag('section',['id'=>'widget-grid']);
ContentViewPanel::begin([
  'padding' => false,
]);
?>

<div class="row">
  <div class="col-xs-12">
    <?php Pjax::begin(['id' => $id . 'PjaxGrid']); ?>

    <?= AdminGridView::widget([
      'dataProvider' => $dataProvider,
      'filterModel' => $searchModel,
      'export' => false,
      'rowOptions' => function ($model) {
        return $model->status === $model::STATUS_INACTIVE ? ['class' => 'danger'] : [];
      },
      'columns' => [
        [
          'attribute' => 'id',
          'contentOptions' => ['style' => 'width: 80px']
        ],
        'code',
        'name',
        [
          'attribute' => 'status',
          'class' => BooleanColumn::class,
          'trueLabel' => Yii::_t("promo.landing_categories.status-active"),
          'falseLabel' => Yii::_t("promo.landing_categories.status-inactive"),
          'filterWidgetOptions' => [
            'pluginOptions' => [
              'allowClear' => true
            ],
            'options' => [
              'placeholder' => '',
            ],
          ],
        ],
        [
          'attribute' => 'is_not_mainstream',
          'class' => BooleanColumn::class,
          'trueLabel' => Yii::_t('app.common.Yes'),
          'falseLabel' => Yii::_t('app.common.No'),
        ],
        [
          'header' => Yii::_t('promo.landings.main'),
          'format' => 'raw',
          'value' => 'landingsLink'
        ],
        [
          'class' => 'mcms\common\grid\ActionColumn',
          'template' => '{view-modal} {update-modal} {disable} {enable}',
          'contentOptions' => ['style' => 'width: 100px'],
        ],

      ],
    ]); ?>
    <?php Pjax::end(); ?>
  </div>
</div>

<?php ContentViewPanel::end() ?>
<?= Html::endTag('section');?>