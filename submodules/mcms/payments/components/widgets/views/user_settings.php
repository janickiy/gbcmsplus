<?php
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\modal\Modal;
use mcms\payments\assets\MultipleCurrencyAssets;
use mcms\payments\components\widgets\assets\UserSettingsAsset;
use mcms\payments\models\search\UserWalletSearch;
use mcms\payments\models\UserPaymentSetting;
use mcms\payments\models\UserWallet;
use mcms\payments\models\wallet\Wallet;
use mcms\payments\Module;
use yii\bootstrap\Html;
use yii\helpers\Url;
use mcms\common\widget\Select2;
use yii\widgets\Pjax;

/** @var UserPaymentSetting $model */
/** @var $currencyList */
/** @var \mcms\payments\models\PartnerCompany $partnerCompany */
/** @var bool $canPartnerChangeWallet */
/** @var bool $canChangeCurrency */
/** @var bool $canUpdatePartnerCompany */
/** @var bool $canViewPartnerCompany */
/** @var string $canChangeCurrencyError */
/** @var bool $canChangeWallet */
/** @var integer $hasPaymentInAwaiting */
/** @var \yii\data\ActiveDataProvider $dataProvider */
/** @var UserWalletSearch $searchModel */
/** @var boolean $showAddSettings */
/** @var bool $canUserHaveWallets может ли пользователь иметь кошельки */
/** @var bool $isAlternativePaymentsGridView вкючен ли альтернативный вид грида. влияет на вывод настройки pay_terms */
/** @var bool $isPartner Партнер? */

UserSettingsAsset::register($this);

$canUseMultipleCurrenciesBalance = $model->canUseMultipleCurrenciesBalance();
$canUseMultipleCurrenciesBalance && MultipleCurrencyAssets::register($this);
$currencyOptions = !$canChangeCurrency || $canUseMultipleCurrenciesBalance
  ? ['disabled' => true, 'title' => $canChangeCurrencyError]
  : [];
$walletOptions = $canChangeWallet ? [] : ['disabled' => true];

$buttons = [];
if ($canChangeWallet) {
  $buttons['update-modal'] = function ($url, $model) {
    return Modal::widget([
      'toggleButtonOptions' => [
        'tag' => 'span',
        'title' => Yii::t('yii', 'Update'),
        'label' => Html::icon('pencil'),
        'class' => 'btn btn-xs btn-default',
        'data-pjax' => 0,
      ],
      'url' => Url::to(['/payments/users/wallet-modal', 'id' => $model->id]),
      'requestMethod' => 'get',
    ]);
  };
}

?>
<div class="panel-body">
  <?php if ($showAddSettings): ?>
      <div id="user-payment-settings-container">
        <?= $this->render('_user_settings_form', compact(
          'model',
          'currencyList',
          'currencyOptions',
          'canChangeCurrency',
          'canViewAdditionalParameters',
          'canCreatePaymentWithoutEarlyCommission',
          'isAlternativePaymentsGridView',
          'isPartner'
        )) ?>
      </div>
  <?php endif;?>
  <?php if ($canUserHaveWallets): ?>


  <?php Pjax::begin(['id' => 'user-payment-settings-pjax-block']); ?>
  <?= Html::beginTag('section', ['id' => 'widget-grid']);
  ContentViewPanel::begin([
    'padding' => false,
    'toolbar' =>
      ($partnerCompany && !$canViewPartnerCompany && !$canUpdatePartnerCompany
        ? Yii::_t('payments.partner-companies.company') . ': ' . $partnerCompany->name . ' '
        : null) .
      ($partnerCompany && $canViewPartnerCompany && !$canUpdatePartnerCompany
        ? Yii::_t('payments.partner-companies.company') . ': ' . Modal::widget([
          'toggleButtonOptions' => [
            'tag' => 'span',
            'label' => $partnerCompany->name,
            'class' => 'btn btn-xs btn-success',
            'data-pjax' => 0,
          ],
          'url' => Url::to(['/payments/partner-companies/view-modal', 'id' => $partnerCompany->id]),
          'requestMethod' => 'get',
        ]) . ' '
        : null) .
      ($canUpdatePartnerCompany
        ? Yii::_t('payments.partner-companies.company') . ': ' . Modal::widget([
          'toggleButtonOptions' => [
            'tag' => 'span',
            'label' => $partnerCompany
              ? $partnerCompany->name
              : Html::icon('plus') . ' ' . Yii::_t('payments.partner-companies.add'),
            'class' => 'btn btn-xs btn-success',
            'data-pjax' => 0,
          ],
          'url' => $partnerCompany
            ?  Url::to(['/payments/partner-companies/update-modal', 'id' => $partnerCompany->id])
            : Url::to(['/payments/partner-companies/create', 'user_id' => $model->user_id]),
          'requestMethod' => 'get',
        ]) . ' '
        : null) .
      ($renderCreateButton
      ? Modal::widget([
        'toggleButtonOptions' => [
          'tag' => 'span',
          'label' => Html::icon('plus') . ' ' . Yii::_t('payments.settings.add_wallet'),
          'class' => 'btn btn-xs btn-success',
          'data-pjax' => 0,
        ],

        'url' => Url::to(['/payments/users/wallet-modal', 'userId' => $model->user_id]),
        'requestMethod' => 'get',
      ])
      : null),
  ]);
  ?>



    <?= AdminGridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'sorter' => [],
    'export' => false,
    'columns' => [
      [
        'attribute' => 'wallet_type',
        'value' => 'walletTypeLabel',
        'enableSorting' => false,
        'filter' => Select2::widget([
          'model' => $searchModel,
          'attribute' => 'wallet_type',
          'data' =>  Wallet::getWallets(null, true),
          'options' => [
            'placeholder' => '',
          ],
          'pluginOptions' => [
            'allowClear' => true,
          ]
        ]),
        'filterOptions' => ['class' => 'column-select'],
      ],
      [
        'attribute' => 'currency',
        'enableSorting' => false,
        'filter' => $model->getCurrencyList(),
        'filterOptions' => ['class' => 'column-select'],
      ],
      [
        'attribute' => 'wallet_account',
        'enableSorting' => false,
        'contentOptions' => ['style' => 'padding:0;border-top:0'],
        'content' => function($model) {
          /**
           * @var $model UserWallet
           */
          return $model->getWalletAccountInfo(['class' => 'wallet-info-table']);
        }
      ],
      [
        'attribute' => 'is_autopayments',
        'class' => '\kartik\grid\BooleanColumn',
        'trueLabel' => Yii::_t('app.common.Yes'),
        'falseLabel' => Yii::_t('app.common.No'),
        'filterWidgetOptions' => [
          'pluginOptions' => [
            'allowClear' => true
          ],
          'options' => [
            'placeholder' => '',
          ],
        ],
      ],
      [
        'attribute' => 'is_verified',
        'visible' => Module::isUserCanVerifyWallets(),
        'class' => '\kartik\grid\BooleanColumn',
        'trueLabel' => Yii::_t('app.common.Yes'),
        'falseLabel' => Yii::_t('app.common.No'),
        'filterWidgetOptions' => [
          'pluginOptions' => [
            'allowClear' => true
          ],
          'options' => [
            'placeholder' => '',
          ],
        ],
      ],
      [
        'attribute' => 'is_visible',
        'label' => UserWallet::translate('attribute-visible'),
        'class' => '\kartik\grid\BooleanColumn',
        'value' => function ($model) {
          /** @var UserWallet $model */
          return !$model->is_deleted;
        },
        'trueLabel' => Yii::_t('app.common.Yes'),
        'falseLabel' => Yii::_t('app.common.No'),
        'filterWidgetOptions' => [
          'pluginOptions' => [
            'allowClear' => true
          ],
          'options' => [
            'placeholder' => '',
          ],
        ],
      ],
      [
        'class' => 'mcms\common\grid\ActionColumn',
        'template' => $canChangeWallet ? '{update-modal} {delete}' : '',
        'buttonsPath' => ['delete' => Url::to('/payments/users/delete-wallet')],
        'controller' => 'promo/personal-profits',
        'contentOptions' => ['class' => 'col-min-width-100'],
        'buttons' => $buttons,
        'visibleButtons' => [
          'update-modal' => function ($model) {
            return !$model->is_deleted;
          },
          'delete' => function ($model) {
            return !$model->is_deleted;
          },
        ],
      ],

    ],
  ]); ?>


  <?php ContentViewPanel::end() ?>
  <?= Html::endTag('section');?>
  <?php Pjax::end(); ?>
  <?php if ($hasPaymentInAwaiting): ?>
      <div class="alert alert-warning">
        <?= Yii::_t('payments.user-payment-settings.message-in-awaiting-changing-not-affect') ?>
      </div>
  <?php endif ?>
  <?php endif;?>
</div>