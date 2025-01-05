<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\components\widgets\LandingsDropdown;
use mcms\common\widget\UserSelect2;
use mcms\promo\models\search\UserOperatorLandingSearch;
use yii\widgets\Pjax;
use mcms\promo\models\Country;
use mcms\common\widget\Select2;
use mcms\promo\models\SourceOperatorLanding;
use mcms\promo\components\LandingOperatorPrices;


/**
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var UserOperatorLandingSearch $searchModel
 */
$userModule = Yii::$app->getModule('users');
?>

<?php ContentViewPanel::begin([
  'padding' => false,
]);
?>

<?php Pjax::begin(['id' => 'landingsPjaxGrid']); ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'columns' => [
    [
      'attribute' => 'user_id',
      'format' => 'raw',
      'value' => function($model) use ($searchModel){
        return $searchModel->getUserLink($model['user_id']);
      },
      'filter' => UserSelect2::widget([
          'model' => $searchModel,
          'attribute' => 'user_id',
          'initValueUserId' => $searchModel->user_id,
          'roles' => [$userModule::PARTNER_ROLE],
          'options' => [
            'placeholder' => '',
          ],
        ]
      ),
    ],
    [
      'attribute' => 'operator_id',
      'format' => 'raw',
      'filter' => OperatorsDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'operator_id',
        'options' => [
          'placeholder' => '',
        ],
        'pluginOptions' => ['allowClear' => true],
        'useSelect2' => true
      ]),
      'value' => function($model) use ($searchModel){
        return $searchModel->getOperatorLink($model['operator_id']);
      },
    ],
    [
      'attribute' => 'country_id',
      'format' => 'raw',
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'country_id',
        'data' => Country::getDropdownItems(),
        'options' => [
          'placeholder' => '',
        ],
        'pluginOptions' => [
          'allowClear' => true
        ]
      ]),
      'value' => function($model) use ($searchModel){
        return $searchModel->getCountryLink($model['country_id']);
      },
    ],
    [
      'attribute' => 'landing_id',
      'format' => 'raw',
      'filter' => LandingsDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'landing_id',
        'options' => [
          'placeholder' => '',
        ],
        'pluginOptions' => ['allowClear' => true],
        'useSelect2' => true
      ]),
      'value' => function($model) use ($searchModel){
        return $searchModel->getLandingLink($model['landing_id']);
      },
    ],
    [
      'header' => Yii::_t('promo.landing_operator_price.cpaPrice'),
      'format' => ['currencyDecimal', 'eur'],
      'value' => function($model) use ($searchModel){
        return $searchModel->getCpaPrice($model, 'eur');
      },
    ],
    [
      'header' => Yii::_t('promo.landing_operator_price.revPrice'),
      'format' => ['currencyDecimal', 'eur'],
      'value' => function($model) use ($searchModel){
        return $searchModel->getRebillPrice($model, 'eur');
      }
    ],
    [
      'attribute' => 'is_active',
      'class' => '\kartik\grid\BooleanColumn',
      'trueLabel' => Yii::_t('app.common.yes'),
      'falseLabel' => Yii::_t('app.common.no'),
      'filterWidgetOptions' => [
        'pluginOptions' => [
          'allowClear' => true
        ],
        'options' => [
          'placeholder' => '',
        ],
      ],
    ],
  ],
]); ?>
<?php Pjax::end(); ?>
<?php ContentViewPanel::end();
