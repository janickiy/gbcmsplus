<?php
use mcms\common\helpers\Link;
use yii\helpers\Url;
/** @var boolean $isAutoPayments */
/** @var boolean $isAutopayAvailable */
?>
<?php // TODO Выпилить автовыплаты. Удалить файл ?>

<div class="title">
  <div class="row">
    <div class="col-xs-6">
      <h2><?= Yii::_t('payments.payments-label-auto-payments') ?></h2>
    </div>
    <div class="col-xs-6 text-right">
      <div class="payments-status__pos">
          <div class="payments-status__toggle <?= $isAutopayAvailable ? '' : 'disabled' ?> <?= $isAutoPayments ? 'on' : '' ?>"
               data-on="<?= Yii::_t('app.common.on') ?>" data-off="<?= Yii::_t('app.common.off') ?>"
               data-enable-url="<?= Url::to(['enable-auto-payments']) ?>"
               data-disable-url="<?= Url::to(['disable-auto-payments']) ?>">
          <span></span>
        </div>
        <i
          data-toggle="tooltip"
          data-placement="left"
          title=""
          class="icon-question"
          data-original-title="<?= Yii::_t('payments.payments-message-auto-payment-scope') ?>"
        ></i>
      </div>
    </div>
  </div>
</div>