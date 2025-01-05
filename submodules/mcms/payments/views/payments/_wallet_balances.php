<?php
/** @var $balance array */
use yii\helpers\ArrayHelper;

$balance = [
  'balances' => [
    'WMR' => ['amount' => 1212313, 'currency' => 'rub'],
    'WMZ' => ['amount' => 1213, 'currency' => 'usd']
  ],
  'error' => false
];
?>

<?php if ($balance): ?>
  <?php if ($balance['error']): ?>
    <div class="alert alert-warning ">
      <i class="fa-fw fa fa-warning"></i>
      <?= $balance['error']; ?>
    </div>
  <?php endif; ?>

    <?php foreach ($balance['balances'] as $wallet => $data): ?>
      <li class="sparks-info" title="<?= Yii::_t('payments.wallet-balances') ?>">
        <h5> <?= $wallet ?>
          <span class="txt-color-blue">
            <?= Yii::$app->formatter->asPrice(
              ArrayHelper::getValue($data, 'amount', 0),
              ArrayHelper::getValue($data, 'currency')
            ) ?>
          </span>
        </h5>
      </li>
    <?php endforeach ?>

<?php endif ?>