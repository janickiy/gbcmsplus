<?php
use mcms\partners\components\widgets\PriceWidget;
use mcms\partners\models\EarlyPaymentRequestForm;
use yii\helpers\Url;

/** @var EarlyPaymentRequestForm $paymentForm */
/** @var bool $canRequirePayments */
?>
<?php // TODO Выпилить автовыплаты. Удалить файл ?>
<?php if ($paymentForm): ?>
  <?php $amount = $paymentForm->getResultAmount() ?>
  <div class="payments content__position payments-order">
    <form action="<?= Url::to(['request-early-payment']) ?>" id="require-early-payment">
      <div class="row">
        <?php if (is_null($walletAccount)): //нет кошелька ?>
          <div class="col-xs-7">
            <p><a href="/partners/profile/finance/" class="add_wallet"><?= Yii::_t('partners.payments.payments-add-wallets-details') ?></a></p>
          </div>
        <?php else: ?>
        <div class="col-xs-7">
          <p><?= Yii::_t('partners.payments.payments-commission') ?>
            <?= (float)$paymentForm->earlyPaymentPercent ?>% — <span id="require-payment-amount"><?= Yii::_t('partners.payments.payments-label-to-payment') ?>
              <?= PriceWidget::widget([
                'value' => $amount,
                'currency' => $balance->getCurrency()
              ]) ?>
            </span>
          </p>
        </div>
        <?php endif;?>
        <div class="col-xs-5 text-right">
          <button class="btn btn-default" <?= $canRequirePayments ? '' : 'disabled' ?>>
            <?= Yii::_t('payments.payments-order') ?>
          </button>
        </div>
      </div>
    </form>
  </div>
<?php endif ?>