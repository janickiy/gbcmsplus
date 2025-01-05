<?php
/** @var $paymentsSettingsForm */

use mcms\common\widget\modal\Modal;
use yii\bootstrap\Html;
use yii\helpers\Url;

?>
<?php $this->beginBlock('actions'); ?>
<?= Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'span',
    'label' => Html::icon('plus') . ' ' . Yii::_t('payments.settings.add_wallet'),
    'class' => 'btn btn-xs btn-success',
    'data-pjax' => 0,
  ],
  'url' => Url::to(['/payments/users/wallet-modal', 'userId' => $userId]),
  'requestMethod' => 'get',
]); ?>
<?php $this->endBlock(); ?>
<div class="row">
  <div class="col-md-12">
    <?= $paymentsSettingsForm ?>
  </div>
</div>