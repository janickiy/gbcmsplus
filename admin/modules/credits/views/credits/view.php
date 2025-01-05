<?php

use admin\modules\credits\models\Credit;
use admin\modules\credits\models\CreditTransaction;
use admin\modules\credits\models\form\CreditPaymentForm;
use admin\modules\credits\widgets\CreditTransactionTypesDropdown;
use mcms\common\grid\ContentViewPanel;
use rgk\theme\smartadmin\widgets\grid\GridView;
use rgk\utils\widgets\AmountRange;
use rgk\utils\widgets\DateRangePicker;
use rgk\utils\widgets\modal\Modal;
use yii\bootstrap\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model admin\modules\credits\models\Credit */
/* @var $transactionsDataProvider \yii\data\ActiveDataProvider */
/* @var $transactionsSearchModel \admin\modules\credits\models\search\CreditTransactionSearch */

$this->title = Credit::t('credit') . ' #' . $model->id;
?>


<div class="row">
  <?php Pjax::begin(['id' => 'credit-transactions-pjax']); ?>
    <div class="col-md-8">
      <?php ContentViewPanel::begin([
        'toolbar' => CreditPaymentForm::isAvailableByCredit($model)
          ? Modal::widget([
            'toggleButtonOptions' => [
              'label' => Html::icon('plus') . ' ' . CreditTransaction::t('create_payment'),
              'class' => 'btn btn-xs btn-success',
            ],
            'url' => ['/credits/credit-payments/create-modal', 'creditId' => $model->id],
          ])
          : null,
        'padding' => false,
        'header' => CreditTransaction::t('credit_payments'),
      ]); ?>

      <?= GridView::widget([
        'dataProvider' => $transactionsDataProvider,
        'filterModel' => $transactionsSearchModel,
        'options' => ['class' => 'text-nowrap'],
        'columns' => [
          'id',
          [
            'attribute' => 'created_at',
            'format' => 'datetime',
            'filter' => DateRangePicker::widget([
              'model' => $transactionsSearchModel,
              'attribute' => 'createdDateRange',
            ])
          ],
          [
            'attribute' => 'amount',
            'format' => 'html',
            'filter' => AmountRange::widget([
              'model' => $transactionsSearchModel,
              'attribute1' => 'fromAmount',
              'attribute2' => 'toAmount',
            ]),
            'value' => function (CreditTransaction $model) {
              return Yii::$app->formatter->asCurrency($model->amount, $model->credit->currency);
            }
          ],
          [
            'attribute' => 'type',
            'filter' => CreditTransactionTypesDropdown::widget([
              'model' => $transactionsSearchModel,
              'attribute' => 'type',
              'exclude' => [CreditTransaction::TYPE_ACCRUE_AMOUNT]
            ]),
            'value' => 'typeName'
          ],
          [
            'class' => 'rgk\utils\widgets\grid\ActionColumn',
            'visible' => $model->status === Credit::STATUS_ACTIVE,
            'template' => '{update-payment}',
            'contentOptions' => ['style' => 'min-width: 25px'],
            'visibleButtons' => [
              'update-payment' => function (CreditTransaction $transaction) {
                return CreditPaymentForm::isAvailableUpdate($transaction);
              },
            ],
            'buttons' => [
              'update-payment' => function ($url, CreditTransaction $model) {
                return Modal::widget([
                  'toggleButtonOptions' => [
                    'label' => Html::icon('pencil'),
                    'class' => 'btn btn-xs btn-default',
                  ],
                  'url' => ['/credits/credit-payments/update-modal', 'id' => $model->id],
                ]);
              },
            ]
          ],
        ]
      ]); ?>


      <?php ContentViewPanel::end() ?>
    </div>
    <div class="col-md-4">
      <?php ContentViewPanel::begin([
        'padding' => false,
        'header' => Credit::t('credit_information'),
      ]); ?>
      <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
          'id',
          [
            'attribute' => 'amount',
            'format' => 'html',
            'value' => Yii::$app->formatter->asCurrency($model->amount, $model->currency)
          ],
          [
            'attribute' => 'debtSum',
            'format' => 'html',
            'value' => Yii::$app->formatter->asCurrency($model->debtSum, $model->currency)
          ],
          [
            'attribute' => 'paySum',
            'format' => 'html',
            'value' => Yii::$app->formatter->asCurrency($model->paySum, $model->currency)
          ],
          [
            'attribute' => 'feeSum',
            'format' => 'html',
            'value' => Yii::$app->formatter->asCurrency($model->feeSum, $model->currency)
          ],
          'percent',
          [
            'attribute' => 'status',
            'value' => $model->getStatusName()
          ],
          [
            'attribute' => 'decline_reason',
            'value' => $model->decline_reason,
            'visible' => $model->status == Credit::STATUS_DECLINED
          ],
          'created_at:datetime',
          [
            'attribute' => 'closed_at',
            'format' => 'datetime',
            'visible' => !!$model->closed_at
          ],
          [
            'attribute' => 'activated_at',
            'format' => 'datetime',
            'visible' => !!$model->activated_at
          ],
          [
            'attribute' => 'maxPayTime',
            'format' => 'datetime',
            'visible' => !!$model->maxPayTime
          ],
        ],
      ]) ?>
      <?php ContentViewPanel::end() ?>
    </div>
  <?php Pjax::end(); ?>
</div>





