<?php
use yii\widgets\DetailView;

/** @var \mcms\payments\components\merchant\info\BasePayoutInfo $paymentInfo */
?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= Yii::_t('payments.info') ?></h4>
</div>
<div class="modal-body">
  <?= DetailView::widget(['model' => $paymentInfo]) ?>
</div>