<?php

use mcms\payments\models\UserPaymentChunk;
use rgk\theme\smartadmin\widgets\grid\GridView;
use yii\helpers\ArrayHelper;
use kartik\helpers\Html;
use mcms\payments\models\UserPayment;

/** @var $model UserPayment */
/** @var $chunkDataProvider \yii\data\ActiveDataProvider */

$statusValue = $model->getStatusLabel();
$statusValueAdditional = [];

if ($model->invoice_file) {
  $statusValueAdditional[] = Html::a($model::translate('download-invoice-lower'), $model->getUploadedFileUrl('invoice_file'), [
    'target' => '_blank',
    'data-pjax' => 0,
  ]);
}

if ($model->cheque_file) {
  $statusValueAdditional[] = Html::a($model::translate('download-check-lower'), $model->getUploadedFileUrl('cheque_file'), [
    'target' => '_blank',
    'data-pjax' => 0,
  ]);
}

if ($statusValueAdditional) {
  $statusValue .= ' (' . implode(', ', $statusValueAdditional) . ')';
}
?>

<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= Yii::_t('payments.reseller-profit-log.detail') ?></h4>
</div>

<div class="modal-body">
  <?= \yii\widgets\DetailView::widget([
    'model' => $model,
    'attributes' => [
      'id',
      [
        'attribute' => 'user_wallet_id',
        'format' => 'raw',
        'value' => function (UserPayment $model) {
          return ($userWallet = $model->getWallet())
            ? $userWallet->getWalletTypeLabel() .
            ' (' . $userWallet->getAccountObject()->getUniqueValue() . ')'
            : null;
        }
      ],
      [
        'label' => Yii::_t('payments.reseller-profit-log.charged_from_balance'),
        'attribute' => 'request_amount',
        'value' => function ($model) {
          return Yii::$app->formatter->asCurrency($model->request_amount, $model->currency);
        }
      ],
      [
        'label' => Yii::_t('payments.reseller-profit-log.paid'),
        'format' => 'raw',
        'value' => function ($model) {
          $original = Yii::$app->formatter->asCurrency($model->amount);

          $currencyIcon = Yii::$app->formatter->asCurrencyIcon($model->currency);

          if ($model->remainSum == (float)$model->amount) {
            return $original . ' ' . $currencyIcon;
          }

          if ($model->remainSum == 0) {
            return $original . ' ' . $currencyIcon;
          }

          $string = Html::tag('abbr', Yii::$app->formatter->asCurrency($model->remainSum), [
            'title' => Yii::_t('payments.reseller-profit-log.remain_tooltip')
          ]);
          $string .= ' (' . $original . ')';
          $string .= ' ' . $currencyIcon;
          return $string;
        },
      ],
      [
        'label' => Yii::_t('payments.user-payments.commission'),
        'value' => function($model) { /* @var UserPayment $model*/

          $commissionPercent = floatval(ArrayHelper::getValue(Yii::$app->params['paysystem-percents'],
              $model->walletModel->code)) + floatval($model->reseller_individual_percent);
          $commission = round($model->request_amount * $commissionPercent/100, 2);
          return $commissionPercent != 0
            ? ($commission > 0 ? '+' : '') . Yii::$app->formatter->asCurrency($commission, $model->currency) .
            ($model->reseller_individual_percent != 0 ? ' (' . ($commissionPercent) . '%)' : '')
            : '';
        },
        'visible' => ArrayHelper::getValue(Yii::$app->params['paysystem-percents'], $model->walletModel->code)
          + $model->reseller_individual_percent != 0
      ],
      [
        'label' => Yii::_t('payments.payout-info.paysystem_percent'),
        'value' => (ArrayHelper::getValue(Yii::$app->params['paysystem-percents'], $model->walletModel->code) > 0
            ? '+' : '') .
          round(ArrayHelper::getValue(Yii::$app->params['paysystem-percents'], $model->walletModel->code),2) . ' %',
        'visible' => floatval(ArrayHelper::getValue(Yii::$app->params['paysystem-percents'], $model->walletModel->code)) != 0
      ],
      [
        'label' => Yii::_t('payments.payout-info.individual_percent'),
        'value' => ($model->reseller_individual_percent > 0 ? '+' : '') .
          round($model->reseller_individual_percent, 2) . ' %',
        'visible' => $model->reseller_individual_percent != 0
      ],
      [
        'attribute' => 'response',
        'visible' => $model->response != null
      ],
      [
        'attribute' => 'description',
        'visible' => $model->description != null
      ],
      [
        'attribute' => 'status',
        'format' => 'raw',
        'value' => $statusValue,
      ],
      [
        'attribute' => 'created_at',
        'format' => 'dateTime'
      ],
      [
        'attribute' => 'updated_at',
        'format' => 'dateTime'
      ],
      [
        'attribute' => 'pay_period_end_date',
        'format' => 'date'
      ]
    ]
  ]) ?>

  <?php if ($model->getChunksSum()) { ?>
    <h1><?= Yii::_t('payments.reseller-profit-log.partial_payments') ?>
      <small><?= Yii::$app->formatter->asCurrency($model->getChunksSum(), $model->currency) ?></small>
    </h1>
    <?= GridView::widget([
      'dataProvider' => $chunkDataProvider,
      'tableOptions' => ['class' => 'text-nowrap'],
      'columns' => [
        [
          'attribute' => 'id',
          'enableSorting' => false
        ],
        [
          'attribute' => 'created_at',
          'format' => 'datetime',
          'enableSorting' => false
        ],
        [
          'attribute' => 'amount',
          'content' => function (UserPaymentChunk $chunk) use ($model) {
            return Yii::$app->formatter->asCurrency($chunk->amount, $model->currency);
          },
          'enableSorting' => false
        ],
      ],
    ]); ?>
  <?php } ?>
</div>
