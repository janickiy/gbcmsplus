<?php

use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AdminGridView;
use mcms\payments\models\wallet\Wallet;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel mcms\payments\models\wallet\WalletSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$limitsFormatter = clone Yii::$app->formatter;
$limitsFormatter->nullDisplay = Yii::_t('wallets.unlimited');
?>

<?php ContentViewPanel::begin([
  'padding' => false,
]);
?>
<?php Pjax::begin(['id' => 'walletsPjaxGrid']); ?>

<?= AdminGridView::widget([
  'id' => 'walletsGrid',
  'dataProvider' => $dataProvider,
  'export' => false,
  'formatter' => $limitsFormatter,
  'columns' => [
    'id',
    [
      'attribute' => 'name',
      'enableSorting' => false,
    ],
    [
      'attribute' => 'profit_percent',
      'format' => ['percent', 2],
      'value' => function($model) {
        return $model->profit_percent/100;
      }
    ],
    [
      'header' => Yii::_t('payments.wallets.available_currencies'),
      'format' => 'raw',
      'value' => function($model) use ($limitsFormatter) {
        return $limitsFormatter->asCurrenciesList($model->getCurrencies(false), $model->getCurrencies());
      }
    ],
    [
      'attribute' => 'min_payout_sum',
      'format' => 'html',
      'content' => function ($model) use ($limitsFormatter) {
        return $limitsFormatter->asPricesByModel($model, '%s_min_payout_sum', '<br/>', $model->getCurrencies());
      }
    ],
    [
      'attribute' => 'max_payout_sum',
      'format' => 'html',
      'content' => function ($model) use ($limitsFormatter) {
        return $limitsFormatter->asPricesByModel($model, '%s_max_payout_sum', '<br/>', $model->getCurrencies());
      }
    ],
    [
      'attribute' => 'payout_limit_daily',
      'format' => 'html',
      'content' => function ($model) use ($limitsFormatter) {
        return $limitsFormatter->asPricesByModel($model, '%s_payout_limit_daily', '<br/>', $model->getCurrencies());
      }
    ],
    [
      'attribute' => 'payout_limit_monthly',
      'format' => 'html',
      'content' => function ($model) use ($limitsFormatter) {
        return $limitsFormatter->asPricesByModel($model, '%s_payout_limit_monthly', '<br/>', $model->getCurrencies());
      }
    ],
    [
      'attribute' => 'is_active',
      'class' => '\kartik\grid\BooleanColumn',
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{update-modal} {enable} {disable}',
      'contentOptions' => ['class' => 'col-min-width-100'],
    ],
  ],
]); ?>
<?php Pjax::end(); ?>
<?php ContentViewPanel::end();


