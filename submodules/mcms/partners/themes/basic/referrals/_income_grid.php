<?php

use yii\widgets\ActiveForm;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\date\DatePicker;
use mcms\partners\components\widgets\PriceWidget;

/* @var \mcms\payments\models\search\ReferralIncomeSearch $searchModel */
/* @var \yii\data\ActiveDataProvider $dataProvider */
/* @var \mcms\common\web\View $this */
/* @var integer $mainAmount */
/* @var integer $holdAmount */
/* @var string $currency */
?>

<?php $form = ActiveForm::begin([
    'id' => 'referral-grid-form',
    'action' => ['income'],
    'method' => 'get',
    'options' => ['data-pjax' => true],
  ]); ?>
<div class="title">
  <span class="referals_grid">
    <?= $form->field($searchModel, 'active_referrals', [
        'template' => '{input}',
        'options' => ['class' => ''],
      ])->dropDownList($searchModel->getActiveReferralsDropdown(), [
          'class' => 'selectpicker bs-select-hidden select_customs',
          'data-width' => '100%',
        ])->label(false) ?>
  </span>
  <div class="payments_daterange">
    <?= DatePicker::widget([
      'model' => $searchModel,
      'attribute' => 'date_from',
      'attribute2' => 'date_to',
      'type' => DatePicker::TYPE_RANGE,
      'layout' => '<span class="input-group-addon">' . Yii::_t('main.from') . '</span>' .
        '{input1}{separator}{input2}',
      'options' => [
        'class' => 'hidden_mobile'
      ],
      'options2' => [
        'class' => 'hidden_mobile'
      ],
      'separator' => Yii::_t('main.to'),
      'pluginOptions' => [
        'format' => 'dd.mm.yyyy',
      ],
      'pluginEvents' => [
        'changeDate' => 'function(e) { setDpDate(e.target.id, true); $("#referral-grid-form").submit()}'
      ]
    ]); ?>
    <div id="dp_mobile" class="input-group input-daterange date_filter">
      <input id="m_referralincomesearch-date_from" type="date" class="form-control" value="">
      <input id="m_referralincomesearch-date_to" type="date" class="form-control" value="">
    </div>
  </div>
</div>
<?php ActiveForm::end(); ?>

<?php Pjax::begin(['id' => 'referrals-grid']); ?>

<?= GridView::widget([
  'layout' => '{items}<div class="text-center border-top">{pager}</div>',
  'dataProvider' => $dataProvider,
  'showFooter' => true,
  'tableOptions' => [
    'class' => 'table table-striped table-payments table-referals'
  ],
  'columns' => [
    [
      'label' => Yii::_t('referrals.id'),
      'footer' => Yii::_t('referrals.total-income'),
      'contentOptions' => [
        'data-label' => Yii::_t('referrals.id'),
      ],
      'value' => function ($model) {
        return '#' . $model->referral->id;
      },
    ],
    [
      'label' => Yii::_t('referrals.registration-date'),
      'value' => 'referral.created_at',
      'format' => ['date', 'php:d.m.Y H:i'],
      'contentOptions' => [
        'data-label' => Yii::_t('referrals.registration-date'),
      ]
    ],
    [
      'encodeLabel' => false,
      'label' => Yii::_t('referrals.income-hold', PriceWidget::widget([
          'currency' => $currency,
      ])),
      'attribute' => 'full_profit_hold',
      'format' => 'decimal',
      'footer' => Yii::$app->formatter->asDecimal($holdAmount),
      'contentOptions' => [
        'data-label' => Yii::_t('referrals.income-hold-label'),
      ]
    ],
    [
      'encodeLabel' => false,
      'label' => Yii::_t('referrals.income', PriceWidget::widget([
          'currency' => $currency,
      ])),
      'attribute' => 'full_profit_main',
      'format' => 'decimal',
      'footer' => Yii::$app->formatter->asDecimal($mainAmount),
      'contentOptions' => [
        'data-label' => Yii::_t('referrals.income-label'),
      ]
    ],
  ]
]); ?>

<?php Pjax::end(); ?>