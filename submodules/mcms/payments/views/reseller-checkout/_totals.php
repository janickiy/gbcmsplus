<?php
use mcms\statistic\components\widgets\assets\TotalsAsset;
use mcms\statistic\models\resellerStatistic\Item;
use mcms\statistic\models\resellerStatistic\ItemSearch;
use yii\web\View;

/** @var View $this */
/** @var Item $item */
/** @var ItemSearch $searchModel */
TotalsAsset::register($this);
?>


  <div class="total-awaiting-payments">
    <div class="total-awaiting-payments__title">
      <?= Yii::_t('statistic.reseller_profit.awaiting_payments') ?>:
    </div>
    <div class="total-awaiting-payments__list total-awaiting-payments__list_collapsed">
      <div class="total-awaiting-payments__item">
        <div class="total-awaiting-payments__item-name">
          <?= Yii::$app->formatter->asCurrency($item->resAwait->getValue('rub'), 'rub')?> (<?= $item->resAwaitCount->getValue('rub')?>)
        </div>
      </div>
      <div class="total-awaiting-payments__item">
        <div class="total-awaiting-payments__item-name">
          <?= Yii::$app->formatter->asCurrency($item->resAwait->getValue('usd'), 'usd')?> (<?= $item->resAwaitCount->getValue('usd')?>)
        </div>
      </div>
      <div class="total-awaiting-payments__item">
        <div class="total-awaiting-payments__item-name">
          <?= Yii::$app->formatter->asCurrency($item->resAwait->getValue('eur'), 'eur')?> (<?= $item->resAwaitCount->getValue('eur')?>)
        </div>
      </div>
    </div>
  </div>

  <div class="total-debt">
    <div class="total-debt__title">
      <?= Yii::_t('statistic.reseller_profit.total_debt') ?>:
    </div>
    <?php $viewPath = '@mcms/statistic/views/reseller-profit/_debt_cell';?>
    <div class="total-debt__list total-debt__list_collapsed">
      <div class="total-debt__item">
        <?= $this->render($viewPath, ['searchModel' => $searchModel, 'currency' => 'rub']) ?>
      </div>
      <div class="total-debt__item">
        <?= $this->render($viewPath, ['searchModel' => $searchModel, 'currency' => 'usd']) ?>
      </div>
      <div class="total-debt__item">
        <?= $this->render($viewPath, ['searchModel' => $searchModel, 'currency' => 'eur']) ?>
      </div>
    </div>
  </div>
<?php
// todo возможно надо вынести в ассет и подключить в лейауте? или сделать виджеты нормальные, а не как у картика
$js = '$("[data-toggle=tooltip]").tooltip({html: true}); 
$("[data-toggle=popover]").popover({html: true})';
$this->registerJs($js);
?>