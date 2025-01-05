<?php

use mcms\common\grid\ContentViewPanel;

/** @var array $resellerBalance */
/** @var array $awaitingSums */
?>

<?php ContentViewPanel::begin([
  'buttons' => [],
  'padding' => false,
  'header' => Yii::_t('payments.reseller-profit-log.balance')
]) ?>

  <div class="block-reseller-balance">
    <table class="table table-bordered table-reseller-balance" style="margin-bottom: 0;"> <?php // без стиля не получается :(?>
      <thead>
      <tr>
        <th><?= Yii::_t('payments.reseller-profit-log.currency')?></th>
        <th><i class="icon-payments"></i> <?= Yii::_t('payments.reseller-profit-log.on_account')?></th>
        <th><i class="fa fa-hourglass-half"></i> <?= Yii::_t('payments.reseller-profit-log.awaiting')?></th>
      </tr>
      </thead>
      <tbody>
      <tr>
        <td>RUB</td>
        <td><strong><?= Yii::$app->formatter->asDecimal($resellerBalance['rub'])?></strong></td>
        <td><?= Yii::$app->formatter->asDecimal($awaitingSums['rub'])?></td>
      </tr>

      <tr>
        <td>USD</td>
        <td><strong><?= Yii::$app->formatter->asDecimal($resellerBalance['usd'])?></strong></td>
        <td><?= Yii::$app->formatter->asDecimal($awaitingSums['usd'])?></td>
      </tr>

      <tr>
        <td>EUR</td>
        <td><strong><?= Yii::$app->formatter->asDecimal($resellerBalance['eur'])?></strong></td>
        <td><?= Yii::$app->formatter->asDecimal($awaitingSums['eur'])?></td>
      </tr>
      </tbody>
    </table>
  </div>

<?php ContentViewPanel::end() ?>