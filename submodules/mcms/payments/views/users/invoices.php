<?php

use mcms\common\grid\ContentViewPanel;
use mcms\user\Module as UserModule;
use rgk\utils\components\CurrenciesValues;
use yii\helpers\Html;
use yii\widgets\Pjax;
use mcms\payments\components\widgets\AmountRange;
use mcms\common\widget\AdminGridView;
use mcms\payments\models\UserBalanceInvoice;
use rgk\utils\widgets\DateRangePicker;
use mcms\payments\models\search\UserBalanceInvoiceSearch;
use mcms\payments\components\UserBalance;
use mcms\common\widget\Select2;
use mcms\promo\components\api\MainCurrencies;
use mcms\common\widget\UserSelect2;
use rgk\utils\widgets\modal\Modal;
use mcms\common\helpers\Link;

/** @var \mcms\common\web\View $this */
/** @var UserBalanceInvoiceSearch $searchModel */
/** @var  \yii\data\ActiveDataProvider $dataProvider */
/** @var CurrenciesValues $footerValues */
$this->title = Yii::_t('payments.menu.partner_invoices');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('actions'); ?>

<?= Link::hasAccess('/payments/payments/add-penalty') ? Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'id' => 'add-penalty',
    'class' => 'btn btn-danger showModalButton clean',
    'label' => Yii::_t('users.penalty')
  ],
  'url' => ['/payments/payments/add-penalty'],
]) : null ?>

<?= Link::hasAccess('/payments/payments/add-compensation') ? Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'id' => 'add-compensation',
    'class' => 'btn btn-success showModalButton clean',
    'label' => Yii::_t('users.compensation')
  ],
  'url' => ['/payments/payments/add-compensation'],
]) : null ?>

<?php $this->endBlock(); ?>

<?php Pjax::begin([
  'id' => 'invoices-pjax',
]); ?>

<?php ContentViewPanel::begin([
  'padding' => false,
]); ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'showFooter' => true,
  'options' => [
    'class' => 'text-nowrap'
  ],
  'rowOptions' => function (UserBalanceInvoice $model) {
    if (in_array($model->type, [UserBalanceInvoice::TYPE_PENALTY, UserBalanceInvoice::TYPE_CONVERT_DECREASE], true)) {
      return ['class' => 'danger'];
    }
    if (in_array($model->type, [UserBalanceInvoice::TYPE_COMPENSATION, UserBalanceInvoice::TYPE_CONVERT_INCREASE], true)) {
      return ['class' => 'success'];
    }
    return '';
  },
  'columns' => [
    'id',
    [
      'attribute' => 'user_id',
      'format' => 'raw',
      'value' => function (UserBalanceInvoice $model) {
        return $model->user->getViewLink();
      },
      'contentOptions' => ['style' => 'width: 200px'],
      'filter' => UserSelect2::widget([
        'model' => $searchModel,
        'attribute' => 'userId',
        'initValueUserId' => $searchModel->userId,
        'roles' => [UserModule::PARTNER_ROLE],
        'options' => ['placeholder' => ''],
      ]),
    ],
    [
      'attribute' => 'date',
      'format' => 'date',
      'filter' => DateRangePicker::widget([
        'model' => $searchModel,
        'attribute' => 'dateDateRange'
      ])
    ],
    [
      'attribute' => 'type',
      'format' => 'raw',
      'filter' => UserBalanceInvoiceSearch::getTypes(),
      'value' => function (UserBalanceInvoice $model) {
        if (empty($model->description)) {
          return $model->getTypeName();
        }

        $info = [];

        if ($model->description) {
          $info[] = $model->description;
        }

        return Html::a(
          $model->getTypeName(),
          'javascript:void(0)',
          [
            'data' => [
              'content' => implode('<br>', $info),
              'toggle' => 'popover',
              'trigger' => 'focus',
              'placement' => 'left',
            ],
            'tabindex' => 0,
            'role' => 'button',
            'class' => 'mcms-popover'
          ]
        );
      }
    ],
    [
      'attribute' => 'amount',
      'format' => 'decimal',
      'filter' => AmountRange::widget([
        'model' => $searchModel,
        'attribute1' => 'fromAmount',
        'attribute2' => 'toAmount',
      ]),
      'contentOptions' => function (UserBalanceInvoice $model) {
        return [
          'style' => 'width: 170px;',
          'class' => $model->amount < 0 ? 'text-danger' : 'text-success'
        ];
      },
      'footer' => implode('<br>', array_filter(array_map(
        function ($currency) use ($footerValues) {
          $value = $footerValues->getValue($currency);
          if (!$value) {
            return false;
          }
          return Html::tag(
            'span',
            Yii::$app->formatter->asCurrency($value, $currency),
            ['class' => $value < 0 ? 'text-danger' : 'text-success']
          );
        },
        [MainCurrencies::RUB, MainCurrencies::USD, MainCurrencies::EUR]
      )))
    ],
    [
      'attribute' => 'currency',
      'format' => 'raw',
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'currency',
        'data' => UserBalance::getCurrencies(),
        'options' => [
          'multiple' => true,
          'placeholder' => '',
        ],
        'pluginOptions' => [
          'allowClear' => true,
        ]
      ]),
    ],
  ]
]); ?>
<?php ContentViewPanel::end() ?>
<?php
// приходится после пиджакс инициализировать заново
// todo возможно надо вынести в ассет и подключить в лейауте? или сделать виджеты нормальные, а не как у картика
$js = <<<JS
$("[data-toggle=tooltip]").tooltip({html: true}); 
$("[data-toggle=popover]").popover({html: true});
JS;
$this->registerJs($js);

?>
<?php Pjax::end();
