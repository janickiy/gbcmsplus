<?php
use yii\grid\GridView;
use mcms\partners\components\mainStat\CompactFormModel;
use yii\helpers\ArrayHelper;
use mcms\statistic\components\mainStat\Group;
use mcms\partners\components\mainStat\Row;

/** @var yii\data\BaseDataProvider $dataProvider */
/** @var CompactFormModel $model */

$groupKey = reset($model->groups);
?>

<?= GridView::widget([
  'dataProvider' => $dataProvider,
  'layout' => '{items}',
  'columns' => [
    [
      'label' => Group::getGroupColumnLabel($groupKey),
      'format' => 'raw',
      'value' => function (Row $row) use ($groupKey) {
        /** @var Group  $group */
        $group = ArrayHelper::getValue($row->getGroups(), $groupKey);
        if (!$group || !$group->getValue()) {
          return null;
        }
        return $group->getFormattedValue();
      },
    ],
    [
      'label' => Yii::_t('statistic.partner_compact_statistic.rebills'),
      'attribute' => 'rebills',
      'format' => 'integer',
    ],
    [
      'label' => Yii::_t('statistic.partner_compact_statistic.buyout'),
      'attribute' => 'soldVisible',
      'format' => 'integer',
    ],
    [
      'label' => Yii::_t('statistic.partner_compact_statistic.ik'),
      'attribute' => 'visibleOnetime',
      'format' => 'integer',
    ],
    [
      'label' => Yii::_t('statistic.partner_compact_statistic.profit', ['currency' => strtoupper($model->getCurrency())]),
      'attribute' => 'partnerTotalProfit',
      'encodeLabel' => false,
      'format' => 'statisticSum',
    ],
  ],
]);