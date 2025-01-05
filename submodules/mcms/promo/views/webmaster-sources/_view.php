<?php

use kartik\grid\GridView;
use mcms\promo\assets\WebmasterSourcesViewAssets;
use mcms\promo\components\LandingOperatorPrices;
use yii\widgets\DetailView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/**
 * @var mcms\promo\models\Source $model
 * @var yii\data\ArrayDataProvider $sourceOperatorLandings
 */

WebmasterSourcesViewAssets::register($this);
?>
<div class="row">
  <div class="col-lg-6 col-md-6 col-sm-6">
    <?= DetailView::widget([
      'model' => $model,
      'id' => 'first_webmaster_detail_view',
      'attributes' => [
        'id',
        'hash',
        [
          'attribute' => 'user_id',
          'format' => 'raw',
          'value' => $model->userLink,
        ],
        [
          'attribute' => 'url',
          'format' => [
            'url', [
              'target' => '_blank'
            ]
          ],
        ],
        [
          'attribute' => 'reject_reason',
        ],
        [
          'attribute' => 'deleted_by',
          'value' => $model->getDeletedByUserName()
        ],
        [
          'attribute' => 'addPrelandOperatorNames',
          'format' => 'raw',
          'value' => $model->getAddPrelandOperatorNames()
        ]
      ]
    ])?>
  </div>
  <div class="col-lg-6 col-md-6 col-sm-6">
    <?= DetailView::widget([
      'model' => $model,
      'attributes' => [
        [
          'attribute' => 'status',
          'format' => 'raw',
          'value' => Html::tag('span', $model->currentStatusName, ['class' => 'bg-' . $model->getStatusColors()[$model->status]])
        ],
        [
          'attribute' => 'category_id',
          'value' =>  $model->currentCategoryName,
        ],
        [
          'attribute' => 'ads_type',
          'value' => $model->currentAdsTypeName,
        ],
        [
          'attribute' => 'created_at',
          'format' => ['datetime']
        ],
        [
          'attribute' => 'updated_at',
          'format' => ['datetime']
        ],
        [
          'attribute' => 'offPrelandOperatorNames',
          'format' => 'raw',
          'value' => $model->getOffPrelandOperatorNames()
        ]
      ]
    ])?>
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
        return '#' . $model['landing']['id'] . ' ' . $model['landing']['name'];
      },
      'width' => '200px'
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