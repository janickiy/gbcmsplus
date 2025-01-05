<?php

use mcms\common\grid\ContentViewPanel;
use mcms\loyalty\models\LoyaltyBonus;
use mcms\loyalty\widgets\BonusStatusesDropdown;
use mcms\loyalty\widgets\BonusTypesDropdown;
use rgk\theme\smartadmin\widgets\grid\GridView;
use rgk\utils\assets\AjaxButtonsAsset;
use rgk\utils\widgets\AmountRange;
use rgk\utils\widgets\DateRangePicker;
use yii\bootstrap\Html;
use yii\widgets\Pjax;
use rgk\utils\widgets\grid\ActionColumn;

/* @var $this yii\web\View */
/* @var $searchModel mcms\loyalty\models\search\LoyaltyBonusSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

AjaxButtonsAsset::register($this);

$this->title = 'Bonuses';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php ContentViewPanel::begin([
  'padding' => false,
]); ?>

<?php Pjax::begin(['id' => 'loyalty-bonuses-pjax']); ?>
<?= GridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'options' => [
    'class' => 'text-nowrap',
  ],
  'rowOptions' => function (LoyaltyBonus $model) {
    if ($model->status === LoyaltyBonus::STATUS_AWAITING) return ['class' => 'warning'];
    if ($model->status === LoyaltyBonus::STATUS_DECLINED) return ['class' => 'danger'];
    return null;
  },
  'columns' => [
    'external_id',
    [
      'attribute' => 'amount_usd',
      'label' => LoyaltyBonus::t('bonus_amount'),
      'format' => 'html',
      'value' => function (LoyaltyBonus $model) {
        return Yii::$app->formatter->asCurrency($model->amount_usd, 'usd');
      },
      'filter' => AmountRange::widget([
        'model' => $searchModel,
        'attribute1' => 'fromAmount',
        'attribute2' => 'toAmount',
      ]),
      'contentOptions' => function () {
        return ['style' => 'width: 100px;'];
      },
    ],
    [
      'attribute' => 'type',
      'filter' => BonusTypesDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'type',
      ]),
      'value' => function (LoyaltyBonus $model) {
        return $model->getTypeName();
      },
    ],
    [
      'label' => LoyaltyBonus::t('bonus_rule'),
      'format' => 'html',
      'contentOptions' => function () {
        return ['style' => 'min-width: 230px; white-space: normal;'];
      },
      'value' => function (LoyaltyBonus $model) {
        $rule = $model->getDetails()->getRuleAsText($model->type, '<br>');
        // Для старых бонусов отображает комментарий инвойса
        return $rule ?: $model->comment;
      }
    ],
    [
      'attribute' => 'created_at',
      'format' => 'datetime',
      'filter' => DateRangePicker::widget([
        'model' => $searchModel,
        'attribute' => 'createdDateRange',
        'align' => DateRangePicker::ALIGN_LEFT,
      ]),
    ],
    [
      'attribute' => 'updated_at',
      'format' => 'datetime',
      'filter' => DateRangePicker::widget([
        'model' => $searchModel,
        'attribute' => 'updatedDateRange',
        'align' => DateRangePicker::ALIGN_LEFT,
      ]),
    ],
    [
      'class' => ActionColumn::class,
      'template' => '{view-modal}',
      'visibleButtons' => [
        'view-modal' => function (LoyaltyBonus $model) {
          return $model->isAvailableDetails();
        }
      ],
      'contentOptions' => ['style' => 'min-width: 30px'],
    ],
  ],
]); ?>

<?php $this->registerJs(<<<JS
  $("[data-toggle=popover]").popover({html: true});
JS
) ?>
<?php Pjax::end(); ?>
<?php ContentViewPanel::end() ?>