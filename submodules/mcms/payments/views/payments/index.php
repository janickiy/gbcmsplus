<?php
use kartik\helpers\Html;
use mcms\common\helpers\Html as McmsHtml;
use mcms\common\widget\AjaxButtons;
use mcms\payments\components\widgets\PartnersAwaitingsPaysums;
use mcms\payments\models\Company;
use mcms\payments\models\wallet\Wallet;
use rgk\utils\widgets\DateRangePicker;
use rgk\utils\widgets\modal\Modal;
use yii\bootstrap\Html as BHtml;
use yii\widgets\Pjax;
use mcms\common\helpers\Link;
use mcms\common\widget\UserSelect2;
use mcms\payments\components\widgets\AmountRange;
use mcms\payments\models\UserPayment;
use mcms\common\widget\Select2;
use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\payments\models\UserPaymentSetting;
use mcms\common\AdminFormatter;

/** @var \mcms\common\web\View $this */
/* @var $searchModel mcms\payments\models\search\UserPaymentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $promoModule \mcms\promo\Module */
/* @var $massPayoutCount int Количество запросов доступных для массовой автовыплаты */
/* @var $isAlternativePaymentsGridView bool Показывать альтернативный вид грида выплат  */

\mcms\payments\assets\GridViewAssets::register($this);
\mcms\payments\assets\PaymentsGridAssets::register($this);
?>
  <div class="row">
    <?php $this->beginBlock('actions'); ?>

    <?= McmsHtml::a(
      BHtml::icon('credit-card') . ' ' . Yii::_t('payments.user-payments.mass_payout_action'),
      ['/payments/payments/mass-payout'],
      [
        'class' => 'btn btn-success btn-xs',
        'title' => !$massPayoutCount ? Yii::_t('payments.user-payments.mass_payout_no_payments_error') : null,
        'disabled' => !$massPayoutCount,
        'data-pjax' => 0,
        AjaxButtons::CONFIRM_ATTRIBUTE => Yii::_t(
          'payments.user-payments.mass_payout_action_confirm',
          ['paymentsCount' => $massPayoutCount]
        ),
        AjaxButtons::AJAX_ATTRIBUTE => 1,
      ]
    ) ?>

    <?= Link::hasAccess('add-penalty') ? Modal::widget([
      'toggleButtonOptions' => [
        'tag' => 'a',
        'id' => 'add-compensation',
        'class' => 'btn btn-danger showModalButton clean',
        'label' => Yii::_t('users.penalty')
      ],
      'url' => ['add-penalty'],
    ]) : null ?>

    <?= Link::hasAccess('add-compensation') ? Modal::widget([
      'toggleButtonOptions' => [
        'tag' => 'a',
        'id' => 'add-compensation',
        'class' => 'btn btn-success showModalButton clean',
        'label' => Yii::_t('users.compensation')
      ],
      'url' => ['add-compensation'],
    ]) : null ?>

    <?= Link::get('create', [], ['class' => 'btn btn-success'],
      Html::icon('plus') . ' ' . Yii::_t('payments.create')
    ); ?>

<?php //= \mcms\common\widget\modal\Modal::widget([
//      'title' => Yii::_t('payments.generate-payments'),
//      'url' => ['payments/generate-payments'],
//      'toggleButtonOptions' => [
//        'tag' => 'a',
//        'class' => 'btn btn-warning showModalButton',
//        'label' => Html::icon('plus') . ' ' . Yii::_t('payments.generate-payments')
//      ],
//    ]) ?>

    <?php
    $link = Link::get('export');

    if ($link) {
      echo \mcms\common\widget\modal\Modal::widget([
        'toggleButtonOptions' => [
          'tag' => 'a',
          'label' => Html::icon('save-file') . ' ' . Yii::_t('export.export-bt'),
          'id' => 'export-modal-button',
          'class' => 'btn btn-info',
          'data-pjax' => 0,
        ],
        'url' => ['export'],
      ]);
    } else {
      echo $link;
    }

    $this->endBlock();
    ?>
  </div>

<?php
$toolbar = admin\widgets\mass_operation\Widget::widget([
  'updatePjaxId' => '#user-payments-grid',
  'selectedElementsDom' => '#payments-grid [name="mass_update[]"]',
  'selectedAllElementsDom' => '#user-payments-grid [name="mass_update_all"]',
  'buttonActionTitle' => Yii::_t('payments.mass-payout'),
  'modalHeader' => Yii::_t('payments.mass-payout'),
  'formView' => '/payments/_mass_payout',
  'formAction' => ['mass-payout'],
  'formModel' => new \mcms\payments\models\forms\MassPayoutForm(),
  'selectionFormAttributeDom' => '[name="MassPayoutForm[selected_id_list]"]'
]);
?>

<?= PartnersAwaitingsPaysums::widget()?>
<?php ContentViewPanel::begin([
  'padding' => false,
  'toolbar' => $toolbar,
]);
?>

<div class="user-payment-index">
  <?php Pjax::begin(['id' => 'user-payments-grid']); ?>

  <?= AdminGridView::widget([
    'id' => 'payments-grid',
    'options' => [
      'class' => 'col-nowrap'
    ],
    'condensed' => true,
    'dataProvider' => $dataProvider,
    'formatter' => ['class' => AdminFormatter::class, 'nullDisplay' => '', 'datetimeFormat' => 'php:d.m.Y H:i:s'],
    'filterModel' => $searchModel,
    'export' => false,
    'showFooter'=> true,
    'footerRowOptions' => ['class' => ''],
    'rowOptions' => function (UserPayment $model) {
      switch ($model->status) {
        case $model::STATUS_AWAITING:
          return ['class' => 'warning'];
        case $model::STATUS_DELAYED:
          $level = $model->getCurrentDelayLevel();
          if ($level === null) return [];
          if ($level === 0) {
            // просрочили
            return ['style' => 'background-color:#f94b4b']; // алый
          }
          if ($level === 1) {
            // ещё есть шанс успеть
            return ['style' => 'background-color:#f17d7d']; // красный
          }
          if ($level === 2) {
            // скоро надо выплатить
            return ['style' => 'background-color:#f7d083']; // orange
          }
          return [];
        case $model::STATUS_ERROR:
          return ['class' => 'danger'];
        case $model::STATUS_CANCELED:
        case $model::STATUS_ANNULLED:
          return ['class' => 'danger'];
        case $model::STATUS_COMPLETED:
          return ['class' => 'success'];
        default:
          return ['class' => ''];
      }
    },
    'columns' => [
      [
        'class' => 'yii\grid\CheckboxColumn',
        'headerOptions' => ['style' => 'padding-right: 0; padding-left: 0 !important'],
        'name' => 'mass_update',
        'checkboxOptions' => function(UserPayment $model) {
          if (!$model->isPayable()) return ['disabled' => 'disabled'];
          return [];
        }
      ],
      [
        'attribute' => 'id',
        'filterOptions' => ['class' => 'column-id'],
        'footer' => Yii::_t('payments.user-payments.total') . ':',
      ],
      [
        'attribute' => 'user',
        'format' => 'raw',
        'value' => function (UserPayment $model) {

          return McmsHtml::a(
            (
            ($model->isUserBlocked && $model->status === UserPayment::STATUS_PROCESS)
              ? Html::icon('info-sign', ['title' => Yii::_t('payments.user-payments.grid-user-is-blocked')])
              : ''
            ) . '#' . $model->user_id . '&nbsp-&nbsp' .
            Yii::$app->formatter->asText($model->user->username),
            Yii::$app->getModule('users')->api('userLink')->buildProfileLink($model->user_id),
            ['data-pjax' => 0],
            ['UsersUserView' => ['userId' => $model->user_id]],
            false
          );
        },
        'filter' => UserSelect2::widget([
          'model' => $searchModel,
          'attribute' => 'user_id',
          'initValueUserId' => $searchModel->user_id,
          'roles' => Yii::$app->getModule('users')->api('roles')->getMainRoles(),
          'options' => [
            'multiple' => true,
            'placeholder' => '',
          ],
        ]),
        'filterOptions' => ['class' => 'column-select'],
      ],
      [
        'attribute' => 'wallet_type',
        'value' => 'walletTypeLabel',
        'filter' => Select2::widget([
          'model' => $searchModel,
          'attribute' => 'wallet_type',
          'data' =>  Wallet::getWallets(),
          'options' => [
            'multiple' => true,
            'placeholder' => '',
          ],
          'pluginOptions' => [
            'allowClear' => true,
          ]
        ]),
        'filterOptions' => ['class' => 'column-select'],
      ],
      [
        'label' => Yii::_t('payments.user-payments.written_off_from_wallet'),
        'attribute' => 'invoice_amount',
        'filter' => AmountRange::widget([
          'model' => $searchModel,
          'attribute1' => 'invoice_amount_from',
          'attribute2' => 'invoice_amount_to',
        ]),
        'value' => function ($model) {
          /* @var UserPayment $model */
          return Yii::$app->formatter->asPrice($model->invoice_amount, $model->invoice_currency);
        },
        'footer' =>  $searchModel->getResultValue('invoice_amount'),
        'contentOptions' => ['style' => 'min-width: 90px;'],
      ],
      [
        'label' => Yii::_t('payments.user-payments.paid'),
        'attribute' => 'amount',
        'value' => function ($model) {
          /* @var UserPayment $model */
          return Yii::$app->formatter->asPrice($model->amount, $model->currency);
        },
        'filter' => AmountRange::widget([
          'model' => $searchModel,
          'attribute1' => 'amount_from',
          'attribute2' => 'amount_to',
        ]),
        'footer' => $searchModel->getResultValue('amount'),
        'contentOptions' => ['style' => 'min-width: 90px;'],
        'filterOptions' => ['class' => 'column-range'],
        'visible' => !$isAlternativePaymentsGridView,
      ],
      [
        'label' => Yii::_t('payments.user-payments.commission'),
        'attribute' => 'commission',
        'value' => function(UserPayment $model) {
          $commission = $model->calcResellerCommission(true);

          return $commission && $commission->amount !== null
            ? Yii::$app->formatter->asPrice($commission->amount, $commission->currency, ['isPlusVisible' => true])
            . ' (' . Yii::$app->formatter->asPercentSimple($commission->percent) . ')'
            : null;
        },
        'contentOptions' => ['style' => 'min-width: 90px;'],
        'visible' => !$isAlternativePaymentsGridView,
      ],
      [
        'attribute' => 'currency',
        'filter' => Select2::widget([
          'model' => $searchModel,
          'attribute' => 'currency',
          'data' => $searchModel->getCurrencyList(),
          'options' => [
            'multiple' => true,
            'placeholder' => '',
          ],
          'pluginOptions' => [
            'allowClear' => true,
          ]
        ]),
        'filterOptions' => ['class' => 'column-select'],
      ],
      [
        'attribute' => 'status',
        'format' => 'raw',
        'value' => function(UserPayment $payment) {
          $value = $payment->getStatusLabel();
          if ($payment->status == UserPayment::STATUS_DELAYED && $payment->pay_period_end_date) {
            $value .= '<br>' . Yii::$app->formatter->asDate($payment->pay_period_end_date);
          }

          if ($payment->invoice_file) {
            $value .= '<br>' . Html::a($payment::translate('download-invoice'), $payment->getUploadedFileUrl('invoice_file'), [
              'target' => '_blank',
              'data-pjax' => 0,
            ]);
          }

          if ($payment->cheque_file) {
            $value .= '<br>' . Html::a($payment::translate('download-check'), $payment->getUploadedFileUrl('cheque_file'), [
              'target' => '_blank',
              'data-pjax' => 0,
            ]);
          }

          return $value;
        },
        'filter' => Select2::widget([
          'model' => $searchModel,
          'attribute' => 'status',
          'data' => $searchModel->getStatuses(),
          'options' => [
            'multiple' => true,
            'placeholder' => '',
          ],
          'pluginOptions' => [
            'allowClear' => true,
          ]
        ]),
        'filterOptions' => ['class' => 'column-select'],
        'contentOptions' => ['style' => 'min-width: 80px;'],
      ],
      [
        'attribute' => 'processing_type',
        'value' => function (UserPayment $model) {
          return $model->getProcessingTypeLabel(true);
        },
        'filter' => Select2::widget([
          'model' => $searchModel,
          'attribute' => 'processing_type',
          'data' => $searchModel->getProcessingTypes(null, true),
          'options' => [
            'multiple' => true,
            'placeholder' => '',
          ],
          'pluginOptions' => [
            'allowClear' => true,
          ]
        ]),
        'filterOptions' => ['class' => 'column-select'],
        'visible' => !$isAlternativePaymentsGridView,
      ],
      [
        'attribute' => 'created_at',
        'format' => 'datetime',
        'filter' => DateRangePicker::widget([
          'model' => $searchModel,
          'attribute' => 'created_at_range',
          'align' => DateRangePicker::ALIGN_LEFT
        ]),
      ],
      [
        'attribute' => 'payed_at',
        'format' => 'datetime',
        'filter' => DateRangePicker::widget([
          'model' => $searchModel,
          'attribute' => 'payed_at_range',
          'align' => DateRangePicker::ALIGN_LEFT
        ]),
      ],
      [
        'attribute' => 'resellerCompany',
        'format' => 'raw',
        'value' => function ($model) {
          $company = $model->resellerCompany;

          return $company ? $company->name : null;
        },
        'filter' => Company::getDropdownList(),
      ],
      [
        'attribute' => 'pay_terms',
        'filter' => UserPaymentSetting::getPayTerms(),
        'value' => function ($model) {
          /** @var $model UserPayment */
          return $model->userPaymentSetting->payTermValue;
        },
        'visible' => $isAlternativePaymentsGridView,
      ],
      [
        'attribute' => 'pay_period_end_date',
        'format' => 'datetime',
        'filter' => DateRangePicker::widget([
          'model' => $searchModel,
          'attribute' => 'pay_period_end_date_range',
          'align' => DateRangePicker::ALIGN_LEFT,
          'todayMaxDate' => false,
        ]),
        'value' => function ($model) {
          /** @var $model UserPayment */
          return $model->status == UserPayment::STATUS_DELAYED
            ? $model->pay_period_end_date
            : null;
        },
        'visible' => $isAlternativePaymentsGridView,
      ],
      [
        'header' => false,
        'class' => mcms\payments\components\grid\ActionColumn::class,
        'template' => '{process-payout-modal} {view} {update}',
        'visibleButtons' => [
          'update' => function ($model) {
            /**
             * @var UserPayment $model
             */
            return $model->canEdit();
          },
          'view' => function ($model) {
            /**
             * @var UserPayment $model
             */
            // показывем кнопку, если может смотреть выплату, но не может провести
            return $model->canView() && (!$model->isPayable() || !Yii::$app->user->can('PaymentsPaymentsProcessPayoutModal'));
          }
        ],
      ],
    ],
  ]); ?>
  <?php Pjax::end(); ?>

</div>

<?php ContentViewPanel::end() ?>
