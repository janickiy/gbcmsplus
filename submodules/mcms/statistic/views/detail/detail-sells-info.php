<?php

use mcms\common\form\AjaxActiveForm;
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use mcms\common\widget\modal\Modal;
use mcms\statistic\components\PopoverWidget;
use yii\helpers\Url;

/** @var \mcms\common\web\View $this */
/** @var \mcms\statistic\models\mysql\DetailStatisticSells $model */
$userModule = Yii::$app->getModule('users');
?>

<?php $promoModule = Yii::$app->getModule('promo');
  $landingId = ArrayHelper::getValue($record, 'landing_id'); ?>

<div class="modal-header">
  <?= Html::button('<span aria-hidden="true">&times;</span>', ['class' => 'close', 'data-dismiss' => 'modal']); ?>
  <h4 class="modal-title"># <?=$record['hit_id']?></h4>
</div>

<div class="modal-body">
<?= \yii\widgets\DetailView::widget([
  'model' => $record,
  'attributes' => [
    [
      'label' => $model->getGridColumnLabel('hit_id'),
      'attribute' => 'hit_id'
    ],
    [
      'label' => $model->getGridColumnLabel('ip'),
      'attribute' => 'ip',
      'visible' => $model->canViewIp(),
      'format' => 'ipFromLong'
    ],
    [
      'label' => $model->getGridColumnLabel('phone_number'),
      'attribute' => 'phone',
      'format' => $model->canViewFullPhone() ? 'raw' : 'protectedPhone',
      'visible' => $model->canViewPhone()
    ],
    [
      'attribute' => 'email',
      'label' => $model->getGridColumnLabel('email'),
      'format' => 'raw',
      'value' => Html::a(
        ArrayHelper::getValue($record, 'email'),
        $userModule->api('userLink')->buildProfileLink(ArrayHelper::getValue($record, 'user_id')),
        ['target' => '_blank', 'data-pjax' => 0],
        ['UsersUserView' => ['userId' => ArrayHelper::getValue($record, 'user_id')]],
        false
      ),
      'visible' => $model->canViewUser()
    ],
    [
      'label' => $model->getGridColumnLabel('stream'),
      'attribute' => 'stream_name',
      'visible' => $model->canViewStream(),
      'format' => 'stringOrNull',
      'value' => Html::a(
        ArrayHelper::getValue($record, 'stream_name'),
        $promoModule->api('url')->viewStream(ArrayHelper::getValue($record, 'stream_id')),
        ['target' => '_blank', 'data-pjax' => 0],
        [],
        false
      )
    ],
    [
      'label' => $model->getGridColumnLabel('source'),
      'attribute' => 'source_name',
      'visible' => $model->canViewSource(),
      'format' => 'stringOrNull',
      'value' => Html::a(
        ArrayHelper::getValue($record, 'source_name'),
        $promoModule->api('url')->viewSource(
          ArrayHelper::getValue($record, 'source_id'),
          ArrayHelper::getValue($record, 'source_type')
        ),
        ['target' => '_blank', 'data-pjax' => 0],
        [],
        false
      )
    ],
    [
      'attribute' => 'landing_name',
      'label' => $model->getGridColumnLabel('landings'),
      'visible' => $model->canViewLanding(),
      'format' => 'stringOrNull',
      'value' => $landingId !== null
        ? Html::a(
            Yii::$app->formatter->asLanding($landingId, ArrayHelper::getValue($record, 'landing_name')),
            $promoModule->api('url')->viewLanding($landingId),
            ['target' => '_blank', 'data-pjax' => 0],
            [],
            false
          )
        : null
    ],
    [
      'label' => $model->getGridColumnLabel('countries'),
      'attribute' => 'country_name',
      'visible' => $model->canViewCountry(),
      'format' => 'stringOrNull',
      'value' => Html::a(
        ArrayHelper::getValue($record, 'country_name'),
        $promoModule->api('url')->viewCountry(ArrayHelper::getValue($record, 'country_id')),
        ['target' => '_blank', 'data-pjax' => 0],
        [],
        false
      )
    ],
    [
      'label' => $model->getGridColumnLabel('operators'),
      'attribute' => 'operator_name',
      'visible' => $model->canViewOperator(),
      'format' => 'stringOrNull',
      'value' => Html::a(
        ArrayHelper::getValue($record, 'operator_name'),
        $promoModule->api('url')->viewOperator(ArrayHelper::getValue($record, 'operator_id')),
        ['target' => '_blank', 'data-pjax' => 0],
        [],
        false
      )
    ],
    [
      'label' => $model->getGridColumnLabel('platforms'),
      'attribute' => 'platform_name',
      'visible' => $model->canViewPlatform(),
      'format' => 'stringOrNull',
      'value' => Html::a(
        ArrayHelper::getValue($record, 'platform_name'),
        $promoModule->api('url')->viewPlatform(ArrayHelper::getValue($record, 'platform_id')),
        ['target' => '_blank', 'data-pjax' => 0],
        [],
        false
      )
    ],
    [
      'label' => $model->getGridColumnLabel('landing_pay_type_name'),
      'attribute' => 'landing_pay_type_name',
    ],
    [
      'label' => $model->getGridColumnLabel('sold_at'),
      'attribute' => 'sold_at',
      'format' => 'gridDate'
    ],
    [
      'attribute' => 'reseller_price_rub',
      'label' => $model->getGridColumnLabel('reseller_price_rub'),
      'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('rub'),
      'value' => $model->getResellerPrice($record, 'rub')
    ],
    [
      'attribute' => 'reseller_price_eur',
      'label' => $model->getGridColumnLabel('reseller_price_eur'),
      'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('eur'),
      'value' => $model->getResellerPrice($record, 'eur')
    ],
    [
      'attribute' => 'reseller_price_usd',
      'label' => $model->getGridColumnLabel('reseller_price_usd'),
      'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('usd'),
      'value' => $model->getResellerPrice($record, 'usd')
    ],
    [
      'attribute' => 'profit_rub',
      'label' => $model->getGridColumnLabel('partner_profit_rub'),
      'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('rub'),
      'value' => $model->getPartnerProfit($record, 'rub')
    ],
    [
      'attribute' => 'profit_eur',
      'label' => $model->getGridColumnLabel('partner_profit_eur'),
      'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('eur'),
      'value' => $model->getPartnerProfit($record, 'eur')
    ],
    [
      'attribute' => 'profit_usd',
      'label' => $model->getGridColumnLabel('partner_profit_usd'),
      'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('usd'),
      'value' => $model->getPartnerProfit($record, 'usd')
    ],
    [
      'attribute' => 'is_visible_to_partner',
      'label' => $model->getGridColumnLabel('is_visible_to_partner'),
      'format' => 'raw',
      'contentOptions' => ['style' => 'min-width: 120px'],
      'value' => PopoverWidget::widget([
        'strings' => $model->getUserData($record)['cpa_profit']
          ? [
              Yii::_t('statistic.partners_cpa_is', [
                'cpa_profit' => $model->getUserData($record)['cpa_profit'],
                'currency' => $model->getUserData($record)['currency'],
                'date' => $model->getUserData($record)['date']
              ]),
              Yii::_t('statistic.diff_is', [
                'period' => $model->getUserData($record)['period'],
                'currency' => $model->getUserData($record)['currency'],
                'diff' => $model->getUserData($record)['diff']
              ]),
              Yii::_t('statistic.' . ($model->getUserData($record)['is_show'] ? 'show_subscribtion' : 'not_show_subscribtion'))
            ]
          : [
              Yii::_t('statistic.partners_cpa_is_empty'),
              Yii::_t('statistic.show_subscribtion')
            ],
        'content' => Yii::_t('app.common.' . (ArrayHelper::getValue($record, 'is_visible_to_partner') ? 'Yes' : 'No')),
        'className' => 'btn btn-xs btn-default',
        'title' => Yii::_t('statistic.check_correction')
      ])
    ],
  ]
]); ?>

  <div class="col-xs-12">
    <?php if($model->canViewReferrer()): ?>
      <strong><?= $model->getGridColumnLabel('referrer') ?>:</strong>
      <p style="word-wrap: break-word"><?= $record['referrer'] ?></p>
    <?php endif;?>
    <?php if($model->canViewUserAgent()): ?>
      <strong><?= $model->getGridColumnLabel('userAgent') ?>:</strong>
      <p style="word-wrap: break-word"><?= $record['userAgent'] ?></p>
    <?php endif;?>
    <?php if($model->canViewSubid() && $record['subid1']): ?>
      <p style="word-wrap: break-word"><strong><?= $model->getGridColumnLabel('subid1') ?>: </strong><?= $record['subid1'] ?></p>
    <?php endif;?>
    <?php if($model->canViewSubid() && $record['subid2']): ?>
      <p style="word-wrap: break-word"><strong><?= $model->getGridColumnLabel('subid2') ?>: </strong><?= $record['subid2'] ?></p>
    <?php endif;?>
    <?php if($model->canViewCid() && $record['getParams']): ?>
      <?php parse_str($record['getParams'], $getParams) ?>
      <p style="word-wrap: break-word"><strong><?= $model->getGridColumnLabel('cid') ?>: </strong><?= ArrayHelper::getValue($getParams, 'cid'); ?></p>
    <?php endif;?>
  </div>
  <div class="clearfix"></div>
</div>
<div class="modal-footer">
  <?php if (!$record['is_visible_to_partner']): ?>
    <?php $form = AjaxActiveForm::begin([
      'action' => ['/statistic/detail/sell-return', 'id' => $record['hit_id']],
      'ajaxSuccess' => Modal::ajaxSuccess('#statistic-pjax'),
    ]); ?>

    <?= Html::submitButton(
      '<i class="fa fa-undo"></i> ' . Yii::_t('statistic.return_to_partner'),
      ['class' => 'btn btn-primary']
    ) ?>
    <?= Html::button(Yii::_t('app.common.Close'), ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) ?>
    <?php AjaxActiveForm::end(); ?>
  <?php else: ?>
    <?= Html::button(Yii::_t('app.common.Close'), ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) ?>
  <?php endif; ?>
</div>