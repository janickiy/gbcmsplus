<?php

use mcms\common\widget\modal\Modal;
use rgk\utils\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use mcms\payments\models\PartnerCompany;
use yii\widgets\Pjax;

/** @var mcms\payments\models\UserPaymentForm $formModel */
/** @var \mcms\payments\models\PartnerCompany $partnerCompany */
?>
<?php $balanceDetail = DetailView::begin([
  'model' => $formModel,
  'attributes' => [
    [
      'attribute' => 'walletTypeLabel',
      'label' => Yii::_t('payments.user-payments.attribute-wallet-type')
    ],
    [
      'attribute' => 'balanceMain',
      'format' => ['price', $formModel->currency],
      'label' => Yii::_t('users.balance-main')
    ],
    [
      'label' => Yii::_t('payments.partner-companies.company'),
      'format' => 'raw',
      'value' => function($formModel) use ($partnerCompany) {
        return ($partnerCompany && !PartnerCompany::isCanView() && !PartnerCompany::isCanManage()
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
                : Url::to(['/payments/partner-companies/create', 'user_id' => $formModel->user_id]),
              'requestMethod' => 'get',
            ])
            : null);
      },
    ],
  ]
]); ?>
<?php Html::addCssClass($balanceDetail->options, 'payment-wallet-info-table') ?>
<?php $balanceDetail::end() ?>
<?php $walletDetail = DetailView::begin([
  'model' => $formModel->paymentAccount,
  'attributes' => $formModel->paymentAccount->getWalletDetailViewAttributes()
]) ?>
<?php Html::addCssClass($walletDetail->options, 'payment-wallet-info-table') ?>
<?php $walletDetail::end() ?>
