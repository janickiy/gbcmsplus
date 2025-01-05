<?php

use mcms\common\helpers\Link;
use mcms\common\widget\AdminGridView;
use mcms\statistic\components\widgets\PartnerStatisticCompact;
use mcms\user\models\UserContact;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\DetailView;

/**
 * @var \mcms\user\models\User $model
 * @var $paymentSettings
 * @var $balance
 * @var $paymentNextDate
 * @var $summaryToPayment
 * @var $isPartner
 * @var $canUserHaveBalance
 * @var $walletAccountInfo
 * @var $promoPersonalProfit
 */
?>

<?php $this->beginBlock('actions') ?>
<?php if (!$model->isNewRecord): ?>
  <?= $model->id ? Html::a(Yii::_t('login_logs.log'), '#', ['data-toggle' => 'modal', 'data-target' => '#login_log', 'class' => 'btn btn-success btn-xs']) : '' ?>
  <?= $model->id ? Link::get('/users/users/login-by-user/', ['id' => $model->id], ['class' => 'btn btn-success btn-xs'], Yii::_t('login.login_by_user')) : '' ?>
<?php endif ?>
<?php $this->endBlock() ?>

<?php $this->beginBlock('subHeader'); // переопределяем заголовок 2го уровня (по дефолту равен активному пункту меню)?>
<?php $this->endBlock(); ?>

<div class="row">
  <div class="col-xs-12 col-sm-12 <?php if ($canUserHaveBalance): ?>col-md-6 col-lg-6<?php endif;?> padding-10">
    <?= DetailView::widget([
      'model' => $model,
      'attributes' => [
        'id',
        'email',
        'topname',
        [
          'attribute' => 'status',
          'format' => 'raw',
          'value' => $model->getNamedStatus(),
        ],
        [
          'attribute' => 'moderationReason',
          'label' => Yii::_t('users.forms.moderation_reason')
        ],
        [
          'label' => Yii::_t('controllers.online_status'),
          'format' => 'raw',
          'value' => $model->isOnline() ? Yii::_t('controllers.online') : Yii::_t('controllers.offline'),
        ],
        'language',
        'phone',
        [
          'label' => Yii::_t('users.forms.user_contacts_title'),
          'format' => 'raw',
          'contentOptions' => ['style' => 'padding:0;border-top:0'],
          'value' => AdminGridView::widget([
            'dataProvider' => $userContactsDataProvider,
            'export' => false,
            'showHeader' => false,
            'layout' => '{items}',
            'columns' => [
              [
                'attribute' => 'type',
                'value' => function ($model) {
                  /** @var UserContact $model */
                  return $model->getTypeLabel();
                },
              ],
              [
                'attribute' => 'data',
                'format' => 'raw',
                'value' => function ($model) {
                  /** @var UserContact $model */
                  return $model->type ? Html::a($model->data, $model->getBuiltData(), ['target' => '_blank']) : $model->data;
                },
              ],
            ],
          ]),
        ],
        [
          'label' => Yii::_t('main.referral_link'),
          'attribute' => 'referralLink',
        ],
        [
          'label' => Yii::_t('users.forms.referrer_id'),
          'attribute' => 'referrer_id',
          'value' => $model->userHasReferrer() ? $model->referrer->email : null
        ],
        [
          'attribute' => 'comment',
          'label' => Yii::_t('users.forms.comment'),
          'format' => 'raw',
          //'value' => $model->comment,
          //'value' => Html::a('asd', '#', ['id' => 'username'])
          'value' => $model->comment,
        ]
      ]
    ])?>
  </div>
  <?php if ($canUserHaveBalance): ?>
  <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 padding-10">
    <?= DetailView::widget([
      'model' => $paymentSettings,
      'options' => ['class' => 'table table-striped table-bordered detail-view', 'style' => 'margin-bottom:18px;'],
      'attributes' => [
        [
          'attribute' => 'currency',
          'value' => strtoupper($paymentSettings->currency),
        ],
        [
          'attribute' => 'referral_percent',
        ],
      ]
    ])?>
    <?= DetailView::widget([
      'model' => [
        'email' => $model['email'],
        'balance_main' => Yii::$app->getFormatter()->asPrice($balance->getMain(), $balance->currency),
        'balance_hold' => Yii::$app->getFormatter()->asPrice($balance->getHold(), $balance->currency),
        // tricky: Закоменчено в рамках mcms-1633
        //'payment_next_date' => Yii::$app->getFormatter()->asDate($paymentNextDate, 'long'),
        'summary_to_payment' => Yii::$app->getFormatter()->asPrice($summaryToPayment, $balance->currency),
      ],
      'options' => ['class' => 'table table-striped table-bordered detail-view', 'style' => 'margin-bottom:18px;'],
      'attributes' => [
        [
          'format' => 'raw',
          'attribute' => 'balance_main',
          'label' => Yii::_t('users.main.balance-main'),
        ],
        [
          'format' => 'raw',
          'attribute' => 'balance_hold',
          'label' => Yii::_t('users.main.balance-hold'),
          'value' => function($row) use ($model) {
            return Link::get('/payments/users/profit', ['id' => $model->id],
              ['class' => 'text-primary', 'data-pjax' => 0],
              ArrayHelper::getValue($row, 'balance_hold')
            );
          }
        ],/* tricky: Закоменчено в рамках mcms-1633
        [
          'attribute' => 'payment_next_date',
          'label' => Yii::_t('users.main.payment-next-date'),
        ],*/
      ]
    ]) ?>

    <?= PartnerStatisticCompact::widget([
      'userId' => $model->id
    ]) ?>
  </div>
  <?php endif;?>
</div>
<div class="row">
  <div class="col-md-12">
    <?=$promoPersonalProfit;?>
  </div>
</div>

<?= $this->render('_login_log', ['model' => $model]) ?>