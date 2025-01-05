<?php
use rgk\utils\components\CurrenciesValues;
use mcms\statistic\models\resellerStatistic\UnholdPlan;

/** @var UnholdPlan $unholdPlan */
/** @var string $currency */
?>


<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= Yii::_t('statistic.reseller_profit.unhold_plan') ?></h4>
</div>

<div class="modal-body">
  <div class="smart-timeline">
    <ul class="smart-timeline-list">
      <?php foreach ($unholdPlan->getMappedValues() as $date => $countriesValues) { ?>
        <li>
          <div class="smart-timeline-icon">
            <i class="glyphicon glyphicon-time"></i>
          </div>
          <div class="smart-timeline-time">
            <small><?= $date ?></small>
          </div>
          <div class="smart-timeline-content">
            <?php foreach ($countriesValues as $countryValue) {
              /** @var CurrenciesValues $countryValues */
              $countryValues = $countryValue['values'];
              ?>

              <div class="row">
                <?php // todo стили может в ассеты засунуть? ?>
                <div class="col-md-6 text-right text-nowrap" style="overflow: hidden;text-overflow: ellipsis;"><strong><?= (string)$countryValue['country'] ?></strong></div>
                <div class="col-md-6 text-nowrap">
                  <?= Yii::$app->formatter->asCurrency($countryValues->getValue($currency), $currency) ?>
                </div>
              </div>
            <?php } ?>
          </div>
        </li>
      <?php } ?>
    </ul>
  </div>
</div>


