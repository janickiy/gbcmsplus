<?php

use mcms\common\grid\ContentViewPanel;
use mcms\promo\models\Landing;
use mcms\common\widget\AdminGridView;
use yii\widgets\Pjax;

?>

<?php Pjax::begin(['id' => 'webmaster-sources-landings-list']) ?>
<?php ContentViewPanel::begin([
  'padding' => false,
  'header' => Yii::_t('promo.landings.main'),
  'buttons' => [],
]);
?>

  <?= AdminGridView::widget([
    'dataProvider' => $model->landingModels,
    'export' => false,
    'rowOptions' => function ($model) {
      return $model->landing->status == Landing::STATUS_INACTIVE ? ['class' => 'danger'] : [];
    },
    'columns' => [
      [
        'attribute' => 'operator_id',
        'label' => Yii::_t('promo.landings.operator-attribute-operator_id'),
        'format' => 'raw',
        'value' => function ($item) {
          return $item->operator->getViewLink();
        },
      ],
      [
        'attribute' => 'landing_id',
        'label' => Yii::_t('promo.landings.operator-attribute-landing_id'),
        'format' => 'raw',
        'value' => function ($item) {
          return $item->landing->getViewLink();
        }
      ],
      [
        'attribute' => 'profit_type',
        'label' => Yii::_t('promo.sources.attribute-default_profit_type'),
        'value' => function ($item) {
          return $item->profitTypeName;
        }
      ]
    ],
  ]); ?>
<?php ContentViewPanel::end() ?>
<?php Pjax::end() ?>
