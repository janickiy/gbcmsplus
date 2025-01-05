<?php

use kartik\grid\GridView;
use mcms\promo\assets\ArbitrarySourcesViewAssets;
use mcms\promo\components\LandingOperatorPrices;
use mcms\promo\models\Source;
use yii\widgets\DetailView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var mcms\promo\models\Source $model */
ArbitrarySourcesViewAssets::register($this);
?>

<div class="row arbitrary_detail_views_wrap">
    <div class="col-lg-4 col-md-4 col-sm-4">
        <?= DetailView::widget([
          'model' => $model,
          'attributes' => [
            'id',
            [
              'attribute' => 'link',
              'options' => ['style' => 'width: 100px']
            ],
            [
              'attribute' => 'user_id',
              'format' => 'raw',
              'value' => $model->userLink,
            ],
            [
              'attribute' => 'name',
              'options' => ['style' => 'width: 100px']
            ],
            [
              'attribute' => 'stream.name',
              'label' => Yii::_t('promo.sources.attribute-stream_id'),
              'format' => 'raw',
              'value' => $model->streamLink . ' (' . Yii::_t('promo.streams.moderate') . ')'
            ],
            [
              'attribute' => 'domain.url',
              'label' => Yii::_t('promo.sources.attribute-domain_id'),
              'format' => 'raw',
              'value' => $model->domainLink . ' (' . Yii::_t('promo.domains.moderate') . ')'
            ],
            [
              'attribute' => 'reject_reason'
            ],
            [
              'attribute' => 'deleted_by',
              'value' => $model->getDeletedByUserName()
            ],
          ]
        ]) ?>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-3">
        <?= DetailView::widget([
          'model' => $model,
          'attributes' => [
            [
              'attribute' => 'status',
              'format' => 'raw',
              'value' => Html::tag('span', $model->currentStatusName, ['class' => 'bg-' . $model->getStatusColors()[$model->status]])
            ],
            [
              'attribute' => 'trafficback_type',
              'value' => $model->getCurrentTrafficbackTypeName()
            ],
            [
              'attribute' => 'is_trafficback_sell',
              'value' => $model->is_trafficback_sell ? Yii::_t('promo.sources.yes') : Yii::_t('promo.sources.no')
            ],
            'postback_url',
            'trafficback_url',
            'subid1',
            'subid2',
          ]
        ]) ?>
    </div>
    <div class="col-lg-5 col-md-5 col-sm-5">
        <?= DetailView::widget([
          'model' => $model,
          'attributes' => [
            [
              'attribute' => 'is_notify_subscribe',
              'value' => $model->is_notify_subscribe ? Yii::_t('promo.sources.yes') : Yii::_t('promo.sources.no')
            ],
            [
              'attribute' => 'is_notify_rebill',
              'value' => $model->is_notify_rebill ? Yii::_t('promo.sources.yes') : Yii::_t('promo.sources.no')
            ],
            [
              'attribute' => 'is_notify_unsubscribe',
              'value' => $model->is_notify_unsubscribe ? Yii::_t('promo.sources.yes') : Yii::_t('promo.sources.no')
            ],
            [
              'attribute' => 'is_notify_cpa',
              'value' => $model->is_notify_cpa ? Yii::_t('promo.sources.yes') : Yii::_t('promo.sources.no')
            ],
            [
              'attribute' => 'addPrelandOperatorNames',
              'format' => 'raw',
              'value' => $model->getAddPrelandOperatorNames()
            ],
            [
              'attribute' => 'offPrelandOperatorNames',
              'format' => 'raw',
              'value' => $model->getOffPrelandOperatorNames()
            ],
            [
              'attribute' => 'created_at',
              'format' => ['datetime']
            ],
            [
              'attribute' => 'updated_at',
              'format' => ['datetime']
            ],
          ]
        ]) ?>
    </div>
</div>

<?php Pjax::begin(['id' => 'source_operator_gridview_' . $model->id, 'options' => [
  'class' => 'pjax_source_operator_gridview', 'data-key' => $model->id
]]); ?>
<?= GridView::widget([
  'dataProvider' => $sourceOperatorLandings,
  'export' => false,
  'layout'=>"<div class='pagination_sum_wrap'>{pager}{summary}</div>{items}",
  'summaryOptions' => ['class' => 'source_operator_summary'],
  'pager' => [
    'options' => ['class'=>'pagination source_operator_pagination_btn'],
  ],
  'columns' => [
    [
      'label' => Yii::_t('promo.sources_operator_landings.attribute-landing_id'),
      'value' => function($model){
          return Yii::$app->formatter->asText($model['landing']['name']);
      }
    ],
    [
      'label' => Yii::_t('promo.sources_operator_landings.attribute-operator_id'),
      'value' => function($model){
          return $model['landing']->getOperatorNames();
      }
    ],
    [
      'label' => Yii::_t('promo.sources_operator_landings.attribute-profit_type'),
      'value' => function($model){
          return $model['profitTypeName'];
      }
    ],
    [
      'format' => 'raw',
      'label' => Yii::_t('promo.landings.buyout_price_usd'),
      'value' => function($model){
          $prices = LandingOperatorPrices::create($model['landingOperator'], Yii::$app->user->id);
          if ($model['landingOperator']->buyout_price_usd == 0) {
              return Html::tag('span', Yii::$app->formatter->asDecimal($prices->getBuyoutProfit('usd')), ['class' => 'converted_price']);
          }
          return Yii::$app->formatter->asDecimal($prices->getBuyoutProfit('usd'));
      }
    ],
    [
      'format' => 'raw',
      'label' => Yii::_t('promo.landings.buyout_price_eur'),
      'value' => function($model){
          $prices = LandingOperatorPrices::create($model['landingOperator'], Yii::$app->user->id);
          if ($model['landingOperator']->buyout_price_eur == 0) {
              return Html::tag('span', Yii::$app->formatter->asDecimal($prices->getBuyoutProfit('eur')), ['class' => 'converted_price']);
          }
          return Yii::$app->formatter->asDecimal($prices->getBuyoutProfit('eur'));
      }
    ],
    [
      'format' => 'raw',
      'label' => Yii::_t('promo.landings.buyout_price_rub'),
      'value' => function($model){
          $prices = LandingOperatorPrices::create($model['landingOperator'], Yii::$app->user->id);
          if ($model['landingOperator']->buyout_price_rub == 0) {
              return Html::tag('span', Yii::$app->formatter->asDecimal($prices->getBuyoutProfit('rub')), ['class' => 'converted_price']);
          }
          return Yii::$app->formatter->asDecimal($prices->getBuyoutProfit('rub'));
      }
    ],
    [
      'format' => 'raw',
      'label' => Yii::_t('promo.landings.rebill_price_usd'),
      'value' => function($model){
          $prices = LandingOperatorPrices::create($model['landingOperator'], Yii::$app->user->id);
          if ($model['landingOperator']->rebill_price_usd == 0) {
              return Html::tag('span', Yii::$app->formatter->asDecimal($prices->getRebillPrice('usd')), ['class' => 'converted_price']);
          }
          return Yii::$app->formatter->asDecimal($prices->getRebillPrice('usd'));
      }
    ],
    [
      'format' => 'raw',
      'label' => Yii::_t('promo.landings.rebill_price_eur'),
      'value' => function($model){
          $prices = LandingOperatorPrices::create($model['landingOperator'], Yii::$app->user->id);
          if ($model['landingOperator']->rebill_price_eur == 0) {
              return Html::tag('span', Yii::$app->formatter->asDecimal($prices->getRebillPrice('eur')), ['class' => 'converted_price']);
          }
          return Yii::$app->formatter->asDecimal($prices->getRebillPrice('eur'));
      }
    ],
    [
      'format' => 'raw',
      'label' => Yii::_t('promo.landings.rebill_price_rub'),
      'value' => function($model){
          $prices = LandingOperatorPrices::create($model['landingOperator'], Yii::$app->user->id);
          if ($model['landingOperator']->rebill_price_rub == 0) {
              return Html::tag('span', Yii::$app->formatter->asDecimal($prices->getRebillPrice('rub')), ['class' => 'converted_price']);
          }
          return Yii::$app->formatter->asDecimal($prices->getRebillPrice('rub'));
      }
    ],
  ]
]);
?>
<?php Pjax::end(); ?>
