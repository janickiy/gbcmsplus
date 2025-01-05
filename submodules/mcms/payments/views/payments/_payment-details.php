<?php

use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserPayment;
use mcms\common\widget\modal\Modal;
use mcms\payments\Module;
use rgk\utils\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use mcms\payments\models\PartnerCompany;

/** @var stdClass $autoProcess */
/** @var stdClass $rgkProcess */
/** @var stdClass $resellerCommission */

/** @var UserPayment $payment */
/** @var \mcms\payments\models\PartnerCompany $partnerCompany */
/** @var bool $showModalCompany */
/** @var \mcms\common\AdminFormatter $formatter */

$formatter = Yii::$app->formatter;
$resellerCommission = $payment->calcResellerCommission(true);
$autoProcess = $payment->calcAutoProcess();
$rgkProcess = $payment->calcRgkProcess();

$statusValue = $payment->getStatusLabel();
$statusValueAdditional = [];

if ($payment->invoice_file) {
  $statusValueAdditional[] = Html::a($payment::translate('download-invoice-lower'), $payment->getUploadedFileUrl('invoice_file'), [
    'target' => '_blank',
    'data-pjax' => 0,
  ]);
}

if ($payment->cheque_file) {
  $statusValueAdditional[] = Html::a($payment::translate('download-check-lower'), $payment->getUploadedFileUrl('cheque_file'), [
    'target' => '_blank',
    'data-pjax' => 0,
  ]);
}

if ($statusValueAdditional) {
  $statusValue .= ' (' . implode(', ', $statusValueAdditional) . ')';
}
?>

<?= DetailView::widget([
  'model' => $payment,
  'attributes' => [
    [
      'attribute' => 'user',
      'format' => 'raw',
      'value' => $payment->user->getViewLink(),
    ],
    [
      'label' => Yii::_t('payments.partner-companies.company'),
      'format' => 'raw',
      'visible' => (bool)$partnerCompany,
      'value' => function($payment) use ($partnerCompany, $showModalCompany) {
        return $showModalCompany ? (($partnerCompany && !PartnerCompany::isCanView() && !PartnerCompany::isCanManage()
            ? $partnerCompany->name
            : null) .
          ($partnerCompany && PartnerCompany::isCanView() && !PartnerCompany::isCanManage()
            ? Yii::_t('payments.partner-companies.company') . ': ' . Modal::widget([
              'toggleButtonOptions' => [
                'tag' => 'span',
                'label' => $partnerCompany->name,
                'class' => 'btn btn-xs btn-success',
                'data-pjax' => 0,
              ],
              'url' => Url::to(['/payments/partner-companies/view-modal', 'id' => $partnerCompany->id]),
              'requestMethod' => 'get',
            ])
            : null) .
          (PartnerCompany::isCanManage()
            ? Modal::widget([
              'toggleButtonOptions' => [
                'tag' => 'span',
                'label' => $partnerCompany
                  ? $partnerCompany->name
                  : Html::icon('plus') . ' ' . Yii::_t('payments.partner-companies.add'),
                'class' => 'btn btn-xs btn-success',
                'data-pjax' => 0,
              ],
              'url' => $partnerCompany
                ?  Url::to(['/payments/partner-companies/update-modal', 'id' => $partnerCompany->id])
                : Url::to(['/payments/partner-companies/create', 'user_id' => $payment->user->id]),
              'requestMethod' => 'get',
            ])
            : null))
          : Html::a(
            $partnerCompany->name,
            Url::to(['/payments/partner-companies/index', 'PartnerCompanySearch[userId]' => $payment->user->id]),
            ['target' => '_blank']
          );
      },
    ],
    [
      'label' => Yii::_t('payments.partner-companies.reseller_company'),
      'format' => 'raw',
      'visible' => $partnerCompany && $partnerCompany->reseller_company_id,
      'value' => function ($payment) use ($partnerCompany, $showModalCompany) {
        $resellerCompany = $partnerCompany->resellerCompany;

        return $showModalCompany
          ? Modal::widget([
            'toggleButtonOptions' => [
              'tag' => 'span',
              'label' => $resellerCompany->name,
              'class' => 'btn btn-xs btn-success',
              'data-pjax' => 0,
            ],
            'url' => Url::to(['/payments/companies/view-modal', 'id' => $resellerCompany->id]),
          ])
          : Html::a(
            $resellerCompany->name,
            Url::to(['/payments/companies/index', 'CompanySearch[id]' => $resellerCompany->id]),
            ['target' => '_blank']
          );
      },
    ],
    [
      'label' => Yii::_t('user-payments.balance_transaction_negative'),
      'value' => function (UserPayment $model) use ($formatter) {
        $result = $formatter->asPrice($model->invoice_amount, $model->invoice_currency);
        if ($model->isConvert()) {
          $result .= ' (' . $formatter->asPrice($model->request_amount, $model->currency) . ')';
        }

        return $result;
      }
    ],
    [
      'label' => Yii::_t('user-payments.commission_reseller'),
      'format' => 'html',
      'value' => $resellerCommission
        ? $formatter->asPrice($resellerCommission->amount, $resellerCommission->currency, [
          'decorate' => true,
          'isPlusVisible' => true,
          'append' => ' (' . $formatter->asPercentSimple($resellerCommission->paysystem_percent - $resellerCommission->early_percent) . ')',
        ]) .

        // Процент платежной системы
        ($resellerCommission->early_percent ?
          '<br>'
          . $formatter->decorateValue(
            $resellerCommission->paysystem_percent,
            $formatter->asPercentSimple($resellerCommission->paysystem_percent, 2, ['isPlusVisible' => true])
            . ' - ' . Yii::_t('user-payments.paysystem_commission')
          )
          // Процент за досрочную выплату
          . '<br>'
          . $formatter->decorateValue(
            -$resellerCommission->early_percent,
            $formatter->asPercentSimple(-$resellerCommission->early_percent, 2, ['isPlusVisible' => true])
            . ' - ' . Yii::_t('user-payments.early_commission')
          ) : null)
        : null,
    ],
    [
      'label' => Yii::_t('user-payments.ready_to_pay'),
      'value' => $formatter->asPrice($payment->amount, $payment->currency),
    ],

    // Автоматическая выплата
    [
      'label' => Yii::_t('user-payments.reseller_profit'),
      'format' => 'html',
      'value' => $autoProcess
        ? $formatter->asPrice(
          $autoProcess->resellerProfit,
          $autoProcess->currency,
          ['isPlusVisible' => true, 'decorate' => true]
        )
        : null,
      'visible' => $payment->processing_type == $payment::PROCESSING_TYPE_API,
    ],

    // Выплата через РГК
    [
      'label' => Yii::_t('user-payments.reseller_cost'),
      'format' => 'html',
      'value' => $rgkProcess
        ? $formatter->asPrice($rgkProcess->resellerCost, $rgkProcess->currency, [
          'decorate' => true,
          'isPlusVisible' => true,
          'append' => ' ('
            . ($rgkProcess->rgkPaysystemPercent - $resellerCommission->percent != 0
              ? $formatter->asPercentSimple($rgkProcess->rgkPaysystemPercent - $resellerCommission->percent, null, ['decorate' => true]) . ' + '
              : null)
            . $formatter->asPercentSimple($rgkProcess->rgkProcessingPercent, null, ['decorate' => true]) . ')',
        ])
        : null,
      'visible' => $payment->processing_type == $payment::PROCESSING_TYPE_EXTERNAL,
    ],
    [
      'label' => Yii::_t('user-payments.processing_fees'),
      'format' => 'html',
      'value' => $rgkProcess
        ? $formatter->asPrice($rgkProcess->rgkCommission, $rgkProcess->currency, [
          'decorate' => true,
          'isPlusVisible' => true,
          // Общий процент РГК
          'append' => ' (' . $formatter->asPercentSimple($rgkProcess->rgkPercent) . ')'
        ])
        // Процент платежной системы
        . '<br>'
        . $formatter->decorateValue(
          $rgkProcess->rgkPaysystemPercent,
          $formatter->asPercentSimple($rgkProcess->rgkPaysystemPercent, 2, ['isPlusVisible' => true])
          . ' - ' . Yii::_t('user-payments.paysystem_commission')
        )
        // Процент за процессинг
        . '<br>' . $formatter->decorateValue(
          $rgkProcess->rgkProcessingPercent,
          $formatter->asPercentSimple($rgkProcess->rgkProcessingPercent, 2, ['isPlusVisible' => true])
          . ' - ' . Yii::_t('user-payments.rgk_processing_commission')
        )
        : null,
      'visible' => $payment->processing_type == $payment::PROCESSING_TYPE_EXTERNAL,
    ],
    [
      'label' => Yii::_t('user-payments.reseller_pay'),
      'format' => 'html',
      'value' => $rgkProcess
        ? $formatter->asPrice(
          $rgkProcess->resellerFullCost,
          $rgkProcess->currency
        )
        : null,
      'visible' => $payment->processing_type == $payment::PROCESSING_TYPE_EXTERNAL,
    ],

    [
      'label' => Yii::_t('payments.payout-info.individual_percent'),
      'format' => 'html',
      'value' => $formatter->asPercentSimple($payment->reseller_individual_percent, 2, ['decorate' => true, 'isPlusVisible' => true]),
      'visible' => $payment->user_id == UserPayment::getResellerId()
        && $payment->processing_type == UserPayment::PROCESSING_TYPE_EXTERNAL,
    ],
    [
      'attribute' => 'processing_type',
      'value' => $payment->getProcessingTypeLabel(),
    ],
    [
      'label' => UserBalanceInvoice::t('attribute-type'),
      'value' => $payment->payeeInvoice->getTypeName(),
    ],
    [
      'attribute' => 'status',
      'format' => 'raw',
      'value' => $statusValue,
    ],
    [
      'attribute' => 'generated_invoice_file_positive',
      'format' => 'raw',
      'visible' => (bool)$payment->generated_invoice_file_positive,
      'value' => Html::a(
        $payment->generated_invoice_file_positive,
        $payment->getUploadedFileUrl('generated_invoice_file_positive'),
        ['target' => '_blank', 'data-pjax' => 0]
      ),
    ],
    [
      'attribute' => 'generated_invoice_file_negative',
      'format' => 'raw',
      'visible' => (bool)$payment->generated_invoice_file_negative,
      'value' => Html::a(
        $payment->generated_invoice_file_negative,
        $payment->getUploadedFileUrl('generated_invoice_file_negative'),
        ['target' => '_blank', 'data-pjax' => 0]
      ),
    ],
    [
      'attribute' => 'description',
      'format' => 'raw',
    ],
    [
      'attribute' => 'isWalletVerified',
      'format' => 'boolean',
      'contentOptions' => [
        'class' => $payment->getIsWalletVerified() ? 'success' : 'danger',
      ],
      'visible' => Module::isUserCanVerifyWallets(),
    ],
    [
      'format' => 'raw',
      'attribute' => 'info',
      'label' => Yii::_t('user-payments.paysystem') . ': ' . $payment->getWalletTypeLabel(),
      'contentOptions' => ['style' => 'padding:0;border-top:0'],
      'value' => $payment->getAccountDetailView(),
      'visible' => !empty($showWalletDetails),
    ],
//    ['attribute' => 'is_hold', 'value' => $payment->getIsHoldLabel()],
    [
      'attribute' => 'response',
      'format' => 'ntext',
      'visible' => !empty($payment->response),
    ],
    [
      'attribute' => 'error_info',
      'visible' => !empty($payment->error_info),
    ],
    'created_at:datetime',
    'updated_at:datetime',
    'payed_at:datetime',
    [
      'attribute' => 'pay_period_end_date',
      'value' => $payment->pay_period_end_date,
      'label' => Yii::_t('payments.user-payments.attribute-delayed_to'),
      'format' => 'date',
      'visible' => !empty($payment->pay_period_end_date)
    ],
  ],
  'options' => [
    'class' => 'table table-striped table-bordered detail-view'
  ]
]) ?>