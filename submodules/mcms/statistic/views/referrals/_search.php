<?php

use yii\bootstrap\ActiveForm;
use kartik\date\DatePicker;
use yii\bootstrap\Html;
use mcms\common\widget\UserSelect2;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use mcms\common\widget\Select2;

/** @var \mcms\statistic\models\mysql\Statistic $model */
/** @var mcms\common\web\View $this */
?>

<?php $form = ActiveForm::begin([
  'method' => 'GET',
  'layout' => 'inline',
  'options' => [
    'data-pjax' => true
  ],
  'id' => 'statistic-filter-form',
]); ?>
<div class="dt-toolbar">


  <div class="filter_pos">
    <?php if (isset($filterDatePeriods)): ?>
      <div class="btn-group" role="group" aria-label="..." style="width: 100%;">
        <?php if (ArrayHelper::getValue($filterDatePeriods, 'today')): ?>
          <button type="button" class="btn btn-default filter-button<?php if (!ArrayHelper::getValue($filterDatePeriods, 'week')):?> active<?php endif; ?>"
                  data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'today.from') ?>"
                  data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'today.to') ?>"
                  data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'today.from') ?>"
                  data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'today.to') ?>"
            ><?= Yii::_t('statistic.statistic.filter_date_today') ?></button>
        <?php endif ?>

        <?php if (ArrayHelper::getValue($filterDatePeriods, 'yesterday')): ?>
          <button type="button" class="btn btn-default filter-button"
                  data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.from') ?>"
                  data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.to') ?>"
                  data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.from') ?>"
                  data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.to') ?>"
            ><?= Yii::_t('statistic.statistic.filter_date_yesterday') ?></button>
        <?php endif ?>

        <?php if (ArrayHelper::getValue($filterDatePeriods, 'week')): ?>
          <button type="button" class="btn btn-default filter-button active"
                  data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'week.from') ?>"
                  data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'week.to') ?>"
                  data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'week.from') ?>"
                  data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'week.to') ?>"
            ><?= Yii::_t('statistic.statistic.filter_date_week') ?></button>
        <?php endif ?>

        <?php if (ArrayHelper::getValue($filterDatePeriods, 'month')): ?>
          <button type="button" class="btn btn-default filter-button"
                  data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'month.from') ?>"
                  data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'month.to') ?>"
                  data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'month.from') ?>"
                  data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'month.to') ?>"
            ><?= Yii::_t('statistic.statistic.filter_date_month') ?></button>
        <?php endif ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="filter_pos">

    <?= DatePicker::widget([
      'model' => $model,
      'attribute' => 'start_date',
      'attribute2' => 'end_date',
      'type' => DatePicker::TYPE_RANGE,
      'separator' => '<i class="glyphicon glyphicon-calendar"></i>',
      'pluginOptions' => [
        'format' => 'yyyy-mm-dd',
        'autoclose' => true,
        'orientation' => 'bottom'
      ],
      'options' => ['style' => 'width:130px', 'class' => 'auto_filter'],
      'options2' => ['style' => 'width:130px', 'class' => 'auto_filter'],
    ]); ?>

  </div>
  <div class="filter_pos user_filter">
    <?php $this->beginBlockAccessVerifier('users', ['StatisticFilterByUsers']); ?>
    <?= UserSelect2::widget([
      'model' => $model,
      'url' => ['stat-filters/users'],
      'options' => [
        'placeholder' => Yii::_t('statistic.users'),
        'multiple' => true,
        'class' => 'auto_filter',
      ],
      'attribute' => 'users',
      'initValueUserId' => $model->users,
    ]) ?>
    <?php $this->endBlockAccessVerifier(); ?>
  </div>
</div>
<?php ActiveForm::end(); ?>
