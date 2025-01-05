<?php

use kartik\widgets\DatePicker;
use mcms\api\models\Country;
use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\Select2;
use mcms\common\widget\UserSelect2;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\models\SubscriptionsLimit;
use rgk\utils\helpers\Html;
use rgk\utils\widgets\AmountRange;
use rgk\utils\widgets\DateRangePicker;
use rgk\utils\widgets\modal\Modal;
use yii\widgets\Pjax;

/** @var \yii\data\ActiveDataProvider $dataProvider */
/** @var \mcms\promo\models\search\SubscriptionLimitsSearch $searchModel */

/** @var \mcms\user\Module $userModule */

$this->title = Yii::_t('promo.subscription_limits.menu');


$userModule = Yii::$app->getModule('users'); ?>

<?php $this->blocks['actions'] = Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => Html::icon('plus') . ' ' . SubscriptionsLimit::t('create'),
    'class' => 'btn btn-success',
    'data-pjax' => 0,
  ],
  'url' => ['/promo/subscription-limits/create-modal'],
]) ?>

<?php ContentViewPanel::begin([
  'padding' => false,
  'header' => false,
]) ?>
<?php Pjax::begin(['options' => ['id' => 'subscription-limits-grid']]) ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'columns' => [
    [
      'attribute' => 'id',
      'contentOptions' => ['style' => 'width: 100px'],
    ],
    [
      'attribute' => 'country_id',
      'format' => 'raw',
      'value' => 'countryLink',
      'contentOptions' => ['style' => 'width: 200px'],
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'countryId',
        'data' => Country::getDropdownItems(),
        'options' => [
          'placeholder' => '',
        ],
        'pluginOptions' => [
          'allowClear' => true
        ]
      ]),
    ],
    [
      'attribute' => 'operator_id',
      'format' => 'raw',
      'value' => 'operatorLink',
      'contentOptions' => ['style' => 'width: 200px'],
      'filter' => OperatorsDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'operatorId',
        'options' => [
          'placeholder' => '',
        ],
        'pluginOptions' => ['allowClear' => true],
        'useSelect2' => true
      ]),
    ],
    [
      'attribute' => 'user_id',
      'format' => 'raw',
      'value' => 'userLink',
      'contentOptions' => ['style' => 'width: 200px'],
      'filter' => UserSelect2::widget([
        'model' => $searchModel,
        'attribute' => 'userId',
        'initValueUserId' => $searchModel->userId,
        'roles' => [$userModule::PARTNER_ROLE],
        'options' => ['placeholder' => ''],
      ]),
    ],
    [
      'attribute' => 'subscriptions_limit',
      'format' => 'integer',
      'contentOptions' => ['style' => 'width: 150px'],
      'filter' => AmountRange::widget([
        'model' => $searchModel,
        'attribute1' => 'subscriptionsFrom',
        'attribute2' => 'subscriptionsTo',
      ]),
    ],
    ['attribute' => 'created_at',
      'format' => 'datetime',
      'filter' => DateRangePicker::widget([
        'model' => $searchModel,
        'attribute' => 'createdAtRange',
        'align' => DateRangePicker::ALIGN_LEFT
      ]),
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{update-modal} {delete}',
      'contentOptions' => ['class' => 'col-min-width-100'],
    ],
  ],
]) ?>

<?php Pjax::end(); ?>
<?php ContentViewPanel::end() ?>
