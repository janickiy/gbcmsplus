<?php
/**
 * @var $logDataProvider \yii\data\ActiveDataProvider
 * @var $canCreate boolean
 */
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\Select2;
use mcms\payments\models\UserPayment;
use mcms\payments\models\search\UserPaymentSearch;
use mcms\payments\models\wallet\Wallet;
use rgk\utils\helpers\Html;
use rgk\utils\widgets\DateRangePicker;


/** @var UserPaymentSearch $searchModel */
?>

<!-- Лог выплат -->
<?php ContentViewPanel::begin([
  'header' => Yii::_t('payments.reseller-profit-log.log'),
  'buttons' => [],
  'padding' => false,
]) ?>

<?= AdminGridView::widget([
  'dataProvider' => $logDataProvider,
  'filterModel' => $searchModel,
  'options' => ['class' => 'col-nowrap'],
  'rowOptions' => function (UserPayment $model) {
    switch ($model->status) {
      case $model::STATUS_AWAITING:
        return ['class' => 'warning'];
      case $model::STATUS_ERROR:
        return ['class' => 'danger'];
      case $model::STATUS_CANCELED:
      case $model::STATUS_ANNULLED:
        return ['class' => 'danger'];
      case $model::STATUS_COMPLETED:
        return ['class' => 'success'];
      default:
        return ['class' => ''];
    }
  },
  'export' => false,
  'columns' => [
    [
      'attribute' => 'id'
    ],
    [
      'format' => 'dateTime',
      'attribute' => 'created_at',
      'contentOptions' => ['style' => 'min-width: 80px'],
      'filter' => DateRangePicker::widget([
        'model' => $searchModel,
        'attribute' => 'created_at_range',
      ]),
    ],
    [
      'label' => Yii::_t('payments.reseller-profit-log.sum'),
      'attribute' => 'amount',
      'content' => function (UserPayment $payment) {
        $original = Yii::$app->formatter->asCurrency($payment->amount);

        $currencyIcon = Yii::$app->formatter->asCurrencyIcon($payment->currency);

        if ($payment->status == UserPayment::STATUS_COMPLETED) {
          return $original . ' ' . $currencyIcon;
        }

        if ($payment->remainSum == (float)$payment->amount) {
          return $original . ' ' . $currencyIcon;
        }

        if ($payment->remainSum == 0) {
          return $original . ' ' . $currencyIcon;
        }

        $string = Html::tag('span', Yii::$app->formatter->asCurrency($payment->remainSum), [
          'title' => Yii::_t('payments.reseller-profit-log.remain_tooltip'),
          'class' => 'mcms-popover'
        ]);
        $string .= ' (' . $original . ')';
        $string .= ' ' . $currencyIcon;
        return $string;
      },
      'value' => function ($model) {
        return Yii::$app->formatter->asCurrency($model->amount, $model->currency);
      },
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'currency',
        'data' => $searchModel->getCurrencyList(),
        'options' => [
          'multiple' => true,
          'placeholder' => '',
        ],
        'pluginOptions' => [
          'allowClear' => true,
        ]
      ]),
      'filterOptions' => ['class' => 'column-select'],
    ],
    [
      'attribute' => 'status',
      'format' => 'raw',
      'value' => function(UserPayment $payment) {
        $value = $payment->getStatusLabel();
        if ($payment->status == UserPayment::STATUS_DELAYED && $payment->pay_period_end_date) {
          $value .= '<br>' . Yii::$app->formatter->asDate($payment->pay_period_end_date);
        }

        if ($payment->invoice_file) {
          $value .= '<br>' . Html::a($payment::translate('download-invoice'), $payment->getUploadedFileUrl('invoice_file'), [
              'target' => '_blank',
              'data-pjax' => 0,
            ]);
        }

        if ($payment->cheque_file) {
          $value .= '<br>' . Html::a($payment::translate('download-check'), $payment->getUploadedFileUrl('cheque_file'), [
              'target' => '_blank',
              'data-pjax' => 0,
            ]);
        }

        return $value;
      },
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'status',
        'data' => $searchModel->getStatuses(),
        'options' => [
          'multiple' => true,
          'placeholder' => '',
        ],
        'pluginOptions' => [
          'allowClear' => true,
        ]
      ]),
      'filterOptions' => ['class' => 'column-select'],
      'contentOptions' => ['style' => 'min-width: 80px;'],
    ],
    [
      'attribute' => 'wallet_type',
      'value' => function(UserPayment $payment) {
        return $payment->getWalletTypeLabel() . ' (' . $payment->userWallet->getAccountObject()->getUniqueValueProtected() . ')';
      },
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'wallet_type',
        'data' =>  Wallet::getWallets(),
        'options' => [
          'multiple' => true,
          'placeholder' => '',
        ],
        'pluginOptions' => [
          'allowClear' => true,
        ]
      ]),
      'filterOptions' => ['class' => 'column-select'],
    ],
    [
      'attribute' => 'pay_period_end_date',
      'format' => 'date',
      'filter' => DateRangePicker::widget([
        'model' => $searchModel,
        'attribute' => 'pay_period_end_date_range',
        'align' => DateRangePicker::ALIGN_LEFT
      ]),
    ],
    [
      'format' => 'dateTime',
      'attribute' => 'payed_at',
      'contentOptions' => ['style' => 'min-width: 80px'],
      'value' => function (UserPayment $payment) {
        return $payment->status == UserPayment::STATUS_COMPLETED ? $payment->payed_at : null;
      },
      'filter' => DateRangePicker::widget([
        'model' => $searchModel,
        'attribute' => 'payed_at_range',
        'align' => DateRangePicker::ALIGN_LEFT
      ]),
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{view-modal}',
      'visible' => $canCreate,
      'header' => false,
    ]
  ],
]); ?>

<?php ContentViewPanel::end() ?>
<!-- /Лог выплат -->