<?php

use mcms\common\grid\ContentViewPanel;
use mcms\promo\models\Country;
use mcms\promo\models\search\CountrySearch;
use mcms\statistic\components\ResellerUnholdSettingsDescription;
use mcms\statistic\models\ResellerHoldRule;
use rgk\theme\smartadmin\widgets\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;

/** @var ActiveDataProvider $dataProvider */
/** @var CountrySearch $searchModel */
/** @var ResellerHoldRule[] $holdRules */
/** @var bool $mgmpUrlAvailable доступен ли MGMP */

$this->title = Yii::_t('statistic.reseller_profit.hold_rules');
?>

<?php if (!$mgmpUrlAvailable): ?>
  <div class="alert alert-danger">
    <?= Yii::_t('statistic.reseller_profit.unavailable') ?>
  </div>
<?php else: ?>
  <div class="alert alert-info">
    <?= Yii::_t('statistic.reseller_profit.hold_rules_hint') ?>
  </div>

  <?php ContentViewPanel::begin([
    'padding' => false,
    'header' => $this->title
  ]); ?>
  <?php Pjax::begin(['id' => 'credits-pjax', 'timeout' => 5000]); ?>
  <?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
      [
        'attribute' => 'id',
        'label' => Yii::_t('statistic.reseller_profit.hold_rules_country_id'),
        'contentOptions' => ['style' => 'width: 70px']
      ],
      [
        'attribute' => 'name',
        'label' => Yii::_t('statistic.reseller_profit.hold_rules_country_name')
      ],
      [
        'label' => Yii::_t('statistic.reseller_profit.hold_rules'),
        'format' => 'raw',
        'value' => function (Country $country) use ($holdRules) {
          $rule = ArrayHelper::getValue($holdRules, $country->id);
          if (!$rule) return null;
          return ResellerUnholdSettingsDescription::getModelDescription($rule, '<br>');
        }
      ]
    ],
  ]); ?>
  <?php Pjax::end(); ?>
  <?php ContentViewPanel::end() ?>
<?php endif; ?>