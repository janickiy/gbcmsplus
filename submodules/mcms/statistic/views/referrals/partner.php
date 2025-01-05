<?php

use mcms\common\helpers\Html;
use mcms\common\widget\AdminGridView;
use mcms\statistic\assets\StatisticAsset;

StatisticAsset::register($this);
\mcms\common\grid\SortIcons::register($this);
$promoModule = Yii::$app->getModule('promo');
$userModule = Yii::$app->getModule('users');
/** @var \mcms\statistic\models\mysql\Referrals $model */
/** @var \yii\data\ActiveDataProvider $dataProvider */
/** @var array $countriesId */
/** @var array $operatorsId */
/** @var string $exportFileName */

?>
<?php
$formatter = Yii::$app->formatter;
$gridColumns = [
  [
    'label' => $model->getGridColumnLabel('referral_id'),
    'attribute' => 'referral_id',
    'format' => 'raw',
    'footer' => Yii::_t('statistic.statistic_total'),
    'value' => function ($item) use ($model) {
      return Html::a(
        $model->formatUserName($item, 'referral_id'),
        ['/users/users/update/', 'id' => $item['referral_id']],
        ['target' => '_blank', 'data-pjax' => 0]
      );
    },
    'contentOptions' => ['class' => 'text-left']
  ],
  [
    'label' => $model->getGridColumnLabel('profit_rub'),
    'attribute' => 'profit_rub',
    'format' => 'statisticSum',
    'footer' => $formatter->asStatisticSum($model->getResultValue('profit_rub')),
  ],
  [
    'label' => $model->getGridColumnLabel('profit_eur'),
    'attribute' => 'profit_eur',
    'format' => 'statisticSum',
    'footer' => $formatter->asStatisticSum($model->getResultValue('profit_eur')),
  ],
  [
    'label' => $model->getGridColumnLabel('profit_usd'),
    'attribute' => 'profit_usd',
    'format' => 'statisticSum',
    'footer' => $formatter->asStatisticSum($model->getResultValue('profit_usd')),
  ],
];
?>

<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $this->title ?></h4>
</div>
<div class="modal-body">
  <?= AdminGridView::widget([
    'dataProvider' => $dataProvider,
    'resizableColumns' => false,
    'pjax' => true,
    'pjaxSettings' => ['options' => ['id' => 'partner-referrals-table-pjax']],
    'tableOptions' => [
      'id' => 'partner-referrals-table',
      'class' => 'table table-striped nowrap text-center detail-table dataTables_scrollHeadInner',
      'data-empty-result' => Yii::t('yii', 'No results found.')
    ],
    'options' => [
      'class' => 'grid-view',
      'style' => 'overflow:hidden; width: 100%;'  // иначе таблица растягивается за пределы экрана.
    ],
    'showFooter' => true,
    'emptyCell' => 0,
    'columns' => $gridColumns,
  ]); ?>
</div>
