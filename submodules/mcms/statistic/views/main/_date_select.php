<?php
use kartik\date\DatePicker;
use mcms\statistic\components\DatePeriod;
use yii\helpers\ArrayHelper;
use mcms\statistic\components\mainStat\FormModel;
use kartik\form\ActiveForm;

/** @var FormModel $formModel */
/** @var int $maxGroups */
/** @var array $filterDatePeriods */
/** @var ActiveForm $form */
?>

<div class="filter_pos">
  <?= $form->field($formModel, 'forceDatePeriod', ['options' => ['class' => '']])
    ->hiddenInput(['id' => 'statistic-period'])->label(false) ?>

  <div class="btn-group" role="group" aria-label="..." style="width: 100%;">
    <?php if (ArrayHelper::getValue($filterDatePeriods, 'today')) : ?>
      <button data-period="<?= DatePeriod::PERIOD_TODAY ?>" type="button" class="btn btn-default filter-button
            <?= $formModel->forceDatePeriod === DatePeriod::PERIOD_TODAY ? ' active' : null ?>"
              data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'today.from') ?>"
              data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'today.to') ?>"
              data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'today.from') ?>"
              data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'today.to') ?>"
      ><?= Yii::_t('statistic.statistic.filter_date_today') ?></button>
    <?php endif ?>

    <?php if (ArrayHelper::getValue($filterDatePeriods, 'yesterday')) : ?>
      <button data-period="<?= DatePeriod::PERIOD_YESTERDAY ?>" type="button" class="btn btn-default filter-button
            <?= $formModel->forceDatePeriod === DatePeriod::PERIOD_YESTERDAY ? ' active' : null ?>"
              data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.from') ?>"
              data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.to') ?>"
              data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.from') ?>"
              data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.to') ?>"
      ><?= Yii::_t('statistic.statistic.filter_date_yesterday') ?></button>
    <?php endif ?>

    <?php if (ArrayHelper::getValue($filterDatePeriods, 'week')) : ?>
      <button data-period="<?= DatePeriod::PERIOD_LAST_WEEK ?>" type="button" class="btn btn-default filter-button
            <?= $formModel->forceDatePeriod === DatePeriod::PERIOD_LAST_WEEK ? ' active' : null ?>"
              data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'week.from') ?>"
              data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'week.to') ?>"
              data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'week.from') ?>"
              data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'week.to') ?>"
      ><?= Yii::_t('statistic.statistic.filter_date_week') ?></button>
    <?php endif ?>

    <?php if (ArrayHelper::getValue($filterDatePeriods, 'month')) : ?>
      <button data-period="<?= DatePeriod::PERIOD_LAST_MONTH ?>" type="button" class="btn btn-default filter-button
            <?= $formModel->forceDatePeriod === DatePeriod::PERIOD_LAST_MONTH ? ' active' : null ?>"
              data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'month.from') ?>"
              data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'month.to') ?>"
              data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'month.from') ?>"
              data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'month.to') ?>"
      ><?= Yii::_t('statistic.statistic.filter_date_month') ?></button>
    <?php endif ?>
  </div>
</div>

<div class="filter_pos">
  <?= DatePicker::widget([
    'model' => $formModel,
    'attribute' => 'dateFrom',
    'attribute2' => 'dateTo',
    'type' => DatePicker::TYPE_RANGE,
    'separator' => '<i class="glyphicon glyphicon-calendar kv-dp-icon"></i>',
    'pluginOptions' => [
      'format' => 'yyyy-mm-dd',
      'autoclose' => true,
      'orientation' => 'bottom'
    ],
    'options' => ['style' => 'width:130px'],
    'options2' => ['style' => 'width:130px']
  ]);
  ?>
</div>
