<?php

use kartik\helpers\Html;
use kartik\widgets\Spinner;
use mcms\common\AdminFormatter;
use mcms\common\helpers\Link;
use mcms\common\widget\alert\AlertAsset;
use mcms\common\widget\modal\Modal as McmsModal;
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;
use yii\widgets\DetailView;
use yii\widgets\Pjax;
use mcms\payments\models\UserPayment;
/* @var mcms\common\web\View $this */
/* @var mcms\payments\models\UserPaymentForm $model */
/* @var string $isReseller */
/* @var \mcms\payments\models\PartnerCompany $partnerCompany */
/* @var bool $showModalCompany */
$isReseller = !empty($isReseller);

AlertAsset::register($this);
$this->registerJs(<<<JS
  $(document).on('process-payout:success', function() {
    $("#process-payout-modal").hide();
  });

JS
);
?>
<?php $this->beginBlock('actions'); ?>
<?php if ($model->isPayable()): ?>
  <?= McmsModal::widget([
    'toggleButtonOptions' => [
      'id' => 'process-payout-modal',
      'title' => Yii::_t('payments.payments.payout'),
      'tag' => 'span',
      'label' => Yii::_t('payments.payments.payout'),
      'class' => 'btn btn-xs btn-success',
      'data-pjax' => 0,
    ],
    'size' => McmsModal::SIZE_LG,
    'url' => ['payments/process-payout-modal', 'id' => $model->id, 'pjaxContainer' => 'user-payment-detail-view'],
  ]);
  ?>
<?php endif; ?>
<?php $this->endBlock(); ?>
<?php Pjax::begin(['id' => 'user-payment-pjax-block']); ?>
<div class="user-payment-view">
    <div class="row">
        <div class="col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title pull-left"><?= $this->title ?></h3>
                    <div class="clearfix"></div>
                </div>
              <?php Pjax::begin(['id' => 'user-payment-detail-view']); ?>
              <?= $this->render('_payment-details', ['payment' => $model, 'partnerCompany' => $partnerCompany,
                'showModalCompany' => true]) ?>
              <?php Pjax::end() ?>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title pull-left"><?= Yii::_t('payments.info') ?></h3>
                    <div class="clearfix"></div>
                </div>
              <?= DetailView::widget([
                'model' => $model,
                'options' => ['class' => 'payment-wallet-info-table table table-bordered detail-view'],
                'attributes' => [
                  ['attribute' => 'wallet_type', 'value' => $model->getWalletTypeLabel()],
                ]
              ]) ?>

              <h5 class="little-header"><?= Yii::_t('payments.recipient') ?></h5>
              <?= $model->getAccountDetailView(['class' => 'payment-wallet-info-table']) ?>

            </div>
        </div>
    </div>
</div>
<?php Pjax::end(); ?>
<?php Modal::begin(['id' => 'modal']) ?>
<div class="well">
  <?= Spinner::widget(['preset' => 'large']) ?>
</div>
<?php Modal::end() ?>
