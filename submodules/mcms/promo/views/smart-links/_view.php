<?php

use kartik\grid\GridView;
use mcms\promo\assets\ArbitrarySourcesViewAssets;
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
          ]
        ]) ?>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-3">
        <?= DetailView::widget([
          'model' => $model,
          'attributes' => [
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


