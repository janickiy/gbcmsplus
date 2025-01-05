<?php
use mcms\loyalty\models\GrowRule;
use mcms\loyalty\models\LoyaltyBonus;
use mcms\loyalty\models\LoyaltyBonusDetails;
use mcms\loyalty\models\TurnoverRule;
use yii\web\View;
use yii\widgets\DetailView;

/** @var View $this */
/** @var LoyaltyBonus $model */
/** @var LoyaltyBonusDetails $bonusDetails */

$formatter = Yii::$app->formatter;
?>


<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
</div>

<div class="modal-body">
  <?= DetailView::widget([
    'options' => ['class' => 'table table-striped table-bordered detail-view bonus-details-table'],
    'model' => $bonusDetails,
    'attributes' => [
      [
        'label' => LoyaltyBonus::t('bonus_amount'),
        'value' => $formatter->asCurrency($model->amount_usd, 'usd'),
      ],
      [
        'label' => $bonusDetails->getAttributeLabel('turnoverRule'),
        'format' => 'raw',
        'value' => $bonusDetails->getRuleAsText($model->type),
        'visible' => $model->type === TurnoverRule::getCode(),
      ],
      [
        'label' => $bonusDetails->getAttributeLabel('growRule'),
        'format' => 'raw',
        'value' => $bonusDetails->getRuleAsText($model->type),
        'visible' => $model->type === GrowRule::getCode(),
      ],
      [
        'attribute' => 'growPercent',
        'format' => 'percentHandy',
        'visible' => $model->type === GrowRule::getCode(),
      ],
      [
        'attribute' => 'turnoverLastMonth',
        'label' => LoyaltyBonusDetails::t('turnover_by_date', ['date' => $formatter->asDate($bonusDetails->dateLastMonth, 'php:Y M')]),
        'format' => 'raw',
        'value' => $formatter->asCurrency($bonusDetails->turnoverLastMonthSum, 'usd')
          . ' (' . $formatter->asCurrencies($bonusDetails->turnoverLastMonth->toArray()) . ')',
      ],
      [
        'attribute' => 'turnoverBeforeLastMonth',
        'label' => LoyaltyBonusDetails::t('turnover_by_date', ['date' => $formatter->asDate($bonusDetails->dateBeforeLastMonth, 'php:Y M')]),
        'format' => 'raw',
        'value' => $formatter->asCurrency($bonusDetails->turnoverBeforeLastMonthSum, 'usd')
          . ' (' . $formatter->asCurrencies($bonusDetails->turnoverBeforeLastMonth->toArray()) . ')',
        'visible' => $model->type === GrowRule::getCode(),
      ],
      [
        'attribute' => 'turnoverThreeMonthAgo',
        'label' => LoyaltyBonusDetails::t('turnover_by_date', ['date' => $formatter->asDate($bonusDetails->dateThreeMonthAgo, 'php:Y M')]),
        'format' => 'raw',
        'value' => $formatter->asCurrency($bonusDetails->turnoverThreeMonthAgoSum, 'usd')
          . ' (' . $formatter->asCurrencies($bonusDetails->turnoverThreeMonthAgo->toArray()) . ')',
        'visible' => $model->type === GrowRule::getCode(),
      ],
    ],
  ]) ?>
</div>