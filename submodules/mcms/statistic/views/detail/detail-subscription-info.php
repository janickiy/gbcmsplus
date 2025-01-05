<?php
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use yii\widgets\Pjax;

/** @var \mcms\common\web\View $this */
/** @var \mcms\statistic\models\mysql\DetailStatisticSubscriptions $model */
?>



<div class="modal-header">
  <?= Html::button('<span aria-hidden="true">&times;</span>', ['class' => 'close', 'data-dismiss' => 'modal']); ?>
  <h4 class="modal-title"># <?=$record['hit_id']?></h4>
</div>

<div class="modal-body">

  <?php $promoModule = Yii::$app->getModule('promo');
    $landingId = ArrayHelper::getValue($record, 'landing_id'); ?>

  <div class="row" id="subscription-detail-view-wrap">
    <div class="col-lg-4 col-md-4 col-sm-4">
      <?= \yii\widgets\DetailView::widget([
        'model' => $record,
        'attributes' => [
          [
            'label' => $model->getGridColumnLabel('hit_id'),
            'attribute' => 'hit_id'
          ],
          [
            'label' => $model->getGridColumnLabel('phone_number'),
            'attribute' => 'phone',
            'format' => $model->canViewFullPhone() ? 'raw' : 'protectedPhone',
            'visible' => $model->canViewPhone()
          ],
          [
            'attribute' => 'ip',
            'label' => $model->getGridColumnLabel('ip'),
            'visible' => $model->canViewIp(),
            'format' => 'ipFromLong'
          ],
          [
            'attribute' => 'stream_name',
            'label' => $model->getGridColumnLabel('stream'),
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
            'attribute' => 'source_name',
            'label' => $model->getGridColumnLabel('source'),
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
        ]
      ]); ?>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-4">
      <?= \yii\widgets\DetailView::widget([
        'model' => $record,
        'attributes' => [
          [
            'attribute' => 'operator_name',
            'label' => $model->getGridColumnLabel('operators'),
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
            'attribute' => 'platform_name',
            'label' => $model->getGridColumnLabel('platforms'),
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
            'label' => $model->getGridColumnLabel('landingPayType'),
            'attribute' => 'landing_pay_type_name',
          ],
          [
            'attribute' => 'subscribed_at',
            'label' => $model->getGridColumnLabel('subscribed_at'),
            'format' => 'gridDate',
          ],
          [
            'attribute' => 'unsubscribed_at',
            'label' => $model->getGridColumnLabel('unsubscribed_at'),
            'format' => 'gridDate',
          ],
          [
            'attribute' => 'last_rebill_at',
            'label' => $model->getGridColumnLabel('last_rebill_at'),
            'format' => 'gridDate',
          ],
        ]
      ]); ?>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-4">
      <?= \yii\widgets\DetailView::widget([
        'model' => $record,
        'attributes' => [
          [
            'attribute' => 'country_name',
            'label' => $model->getGridColumnLabel('countries'),
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
            'attribute' => 'count_rebills',
            'label' => $model->getGridColumnLabel('rebill_count'),
            'visible' => $model->canViewCountRebills()
          ],
          [
            'attribute' => 'sum_profit_rub',
            'label' => $model->getGridColumnLabel('sum_profit_rub'),
            'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('rub'),
            'value' => $model->getPartnerProfit($record, 'rub')
          ],
          [
            'attribute' => 'sum_profit_eur',
            'label' => $model->getGridColumnLabel('sum_profit_eur'),
            'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('eur'),
            'value' => $model->getPartnerProfit($record, 'eur')
          ],
          [
            'attribute' => 'sum_profit_usd',
            'label' => $model->getGridColumnLabel('sum_profit_usd'),
            'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('usd'),
            'value' => $model->getPartnerProfit($record, 'usd')
          ],
          [
            'attribute' => 'sum_reseller_profit_rub',
            'label' => $model->getGridColumnLabel('sum_reseller_profit_rub'),
            'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('rub'),
            'value' => $model->getResellerProfit($record, 'rub')
          ],
          [
            'attribute' => 'sum_reseller_profit_eur',
            'label' => $model->getGridColumnLabel('sum_reseller_profit_eur'),
            'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('eur'),
            'value' => $model->getResellerProfit($record, 'eur')
          ],
          [
            'attribute' => 'sum_reseller_profit_usd',
            'label' => $model->getGridColumnLabel('sum_reseller_profit_usd'),
            'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('usd'),
            'value' => $model->getResellerProfit($record, 'usd')
          ],
          [
            'attribute' => 'sum_real_profit_rub',
            'label' => $model->getGridColumnLabel('sum_real_profit_rub'),
            'visible' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('rub'),
            'value' => $model->getAdminProfit($record, 'rub')
          ],
          [
            'attribute' => 'sum_real_profit_eur',
            'label' => $model->getGridColumnLabel('sum_real_profit_eur'),
            'visible' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('eur'),
            'value' => $model->getAdminProfit($record, 'eur')
          ],
          [
            'attribute' => 'sum_real_profit_usd',
            'label' => $model->getGridColumnLabel('sum_real_profit_usd'),
            'visible' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('usd'),
            'value' => $model->getAdminProfit($record, 'usd')
          ],
        ]
      ]); ?>
    </div>
  </div>

  <div class="row">
    <div class="col-xs-12">
      <?php if($model->canViewReferrer()): ?>
        <p style="word-wrap: break-word"><strong><?= $model->getGridColumnLabel('referrer') ?>: </strong><?= $record['referrer'] ?></p>
      <?php endif;?>
      <?php if($model->canViewUserAgent()): ?>
        <p style="word-wrap: break-word"><strong><?= $model->getGridColumnLabel('userAgent') ?>: </strong><?= $record['userAgent'] ?></p>
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
  </div>

  <?php $this->beginBlockAccessVerifier('rebillsGrid', ['StatisticViewDetailRebillList']); ?>

  <h4><?= Yii::_t('statistic.statistic.detail-info-legend') ?></h4>
  <?php Pjax::begin(['id' => 'detail-rebills']); ?>
  <?= \yii\grid\GridView::widget([
    'dataProvider' => $rebillsDataProvider,
    'tableOptions' => [
      'class' => 'table table-striped nowrap',
      'id' => 'statistic-data-table-view'
    ],
    'layout' => '{items}{pager}',
    'showFooter' => false,
    'emptyCell' => 0,
    'options' => [
      'class' => 'grid-view',
      'style' => 'overflow:auto'  // иначе таблица растягивается за пределы экрана.
    ],
    'formatter' => ['class' => \mcms\common\AdminFormatter::class, 'nullDisplay' => '0'],
    'columns' => [
      [
        'label' => Yii::_t('statistic.statistic.detail-rebilled-at'),
        'attribute' => 'time',
        'format' => 'dateTime',
      ],

      [
        'label' => $model->getGridColumnLabel('sum_profit_rub'),
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('rub'),
        'value' => function ($row) use ($model) {
          return ArrayHelper::getValue($row, 'profit_rub');
        }
      ],
      [
        'label' => $model->getGridColumnLabel('sum_profit_eur'),
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('eur'),
        'value' => function ($row) use ($model) {
          return ArrayHelper::getValue($row, 'profit_eur');
        }
      ],
      [
        'label' => $model->getGridColumnLabel('sum_profit_usd'),
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('usd'),
        'value' => function ($row) use ($model) {
          return ArrayHelper::getValue($row, 'profit_usd');
        }
      ],
      [
        'label' => $model->getGridColumnLabel('sum_reseller_profit_rub'),
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('rub'),
        'value' => function ($row) use ($model) {
          return ArrayHelper::getValue($row, 'reseller_profit_rub');
        }
      ],
      [
        'label' => $model->getGridColumnLabel('sum_reseller_profit_eur'),
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('eur'),
        'value' => function ($row) use ($model) {
          return ArrayHelper::getValue($row, 'reseller_profit_eur');
        }
      ],
      [
        'label' => $model->getGridColumnLabel('sum_reseller_profit_usd'),
        'visible' => $model->canViewResellerProfit() && $model->canViewColumnByCurrency('usd'),
        'value' => function ($row) use ($model) {
          return ArrayHelper::getValue($row, 'reseller_profit_usd');
        }
      ],
      [
        'label' => $model->getGridColumnLabel('sum_real_profit_rub'),
        'visible' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('rub'),
        'value' => function ($row) use($model) {
          return ArrayHelper::getValue($row, 'real_profit_rub');
        }
      ],
      [
        'label' => $model->getGridColumnLabel('sum_real_profit_eur'),
        'visible' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('eur'),
        'value' => function ($row) use($model) {
          return ArrayHelper::getValue($row, 'real_profit_eur');
        }
      ],
      [
        'label' => $model->getGridColumnLabel('sum_real_profit_usd'),
        'visible' => $model->canViewAdminProfit() && $model->canViewColumnByCurrency('usd'),
        'value' => function ($row) use($model) {
          return ArrayHelper::getValue($row, 'real_profit_usd');
        }
      ],

    ]
  ]); ?>
  <?php Pjax::end() ?>
  <?php $this->endBlockAccessVerifier(); ?>

</div>
<div class="modal-footer">
  <?= Html::button(Yii::_t('app.common.Close'), ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) ?>
</div>


