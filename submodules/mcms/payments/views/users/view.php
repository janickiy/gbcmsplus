<?php

use kartik\helpers\Html;
use kartik\widgets\Spinner;
use mcms\common\helpers\Link;
use mcms\common\widget\alert\AlertAsset;
use mcms\payments\assets\MultipleCurrencyAssets;
use mcms\payments\models\UserPayment;
use mcms\payments\Module;
use mcms\common\widget\AdminGridView as GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\widgets\Pjax;
use mcms\common\widget\modal\Modal;

/** @var mcms\common\web\View $this */
/** @var \mcms\payments\components\UserBalance $balance */
/** @var array $user */
/** @var \yii\data\ActiveDataProvider $paymentsDataProvider */
/** @var integer $paymentNextDate */
/** @var float $summaryToPayment */
/** @var \mcms\payments\models\UserPaymentSetting $userPaymentSettings */


MultipleCurrencyAssets::register($this);

AlertAsset::register($this);
?>


<?php $this->beginBlock('info') ?>
<?php if ($userPaymentSettings->canUseMultipleCurrenciesBalance()): ?>
  <?= Yii::$app->getModule('promo')->api('mainCurrenciesWidget', [
      'type' => 'buttons',
      'containerId' => 'resellerBalanceCurrencySwitcher'
  ])->getResult() ?>
<?php endif ?>
<?php $this->endBlock() ?>

<?php $this->beginBlock('subHeader'); // переопределяем заголовок 2го уровня (по дефолту равен активному пункту меню)?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('actions') ?>

<?= Link::get('payments/create', ['userId' => $user['id']],
  ['class' => 'btn btn-success'],
  Html::icon('rub') . ' ' . Yii::_t('users.create-payment')
) ?>

<?php // TODO следующий код является временным, пока в стате реса не начала учитывать компенсации и штрафы для реса ?>
<?php if (!$userPaymentSettings->canUseMultipleCurrenciesBalance()): ?>
  <?= Modal::widget([
    'toggleButtonOptions' => [
      'tag' => 'a',
      'id' => 'add-compensation',
      'class' => 'btn btn-success showModalButton clean',
      'label' => Yii::_t('users.compensation')
    ],
    'url' => Url::to(['users/add-compensation', 'id' => $user['id']]),
  ]) ?>

  <?= Modal::widget([
    'toggleButtonOptions' => [
      'tag' => 'a',
      'id' => 'add-compensation',
      'class' => 'btn btn-danger showModalButton clean',
      'label' => Yii::_t('users.penalty')
    ],
    'url' => Url::to(['users/add-penalty', 'id' => $user['id']]),
  ]) ?>
<?php endif ?>



<?php $this->endBlock() ?>

<?php Pjax::begin(['id' => 'balance']) ?>
  <div class="row">
    <div class="col-md-8">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title pull-left"><?=Yii::_t('users.payments')?></h3>
          <div class="clearfix"></div>
        </div>
        <div class="panel-body">

            <?php if ($paymentsDataProvider->count): ?>
              <?= GridView::widget([
                  'dataProvider' => $paymentsDataProvider,
                  'formatter' => ['class' => yii\i18n\Formatter::class, 'nullDisplay' => ''],
                  'layout' => "{items}\n{pager}",
                  'columns' => [
                      'id',
                      [
                        'attribute' => 'amount',
                        'format' => 'raw',
                        'value' => function (UserPayment $payment) {
                          return Yii::$app->formatter->asCurrency($payment->amount, $payment->currency);
                        }
                      ],
                      [
                          'attribute' => 'status',
                          'value' => 'statusLabel',
                      ],
                      [
                          'attribute' => 'type',
                          'value' => 'typeLabel',
                      ],
                      'created_at:datetime',
                      'payed_at:datetime',
                      'period',
                      [
                        'attribute' => 'isWalletVerified',
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
                          'class' => mcms\payments\components\grid\ActionColumn::class,
                          'controller' => 'payments/payments',
//                          'template' => '{process-payout-modal} {view} {update}', убрали редактирование в MCMS-1218
                          'template' => '{process-payout-modal} {view}',
                          'buttonsPath' => [
                            'process-payout-modal' => ['/payments/payments/process-payout-modal/', 'pjaxContainer' => 'balance'],
                          ],
                          'filterOptions' => ['class' => 'column-action'],
                      ],
                  ]
              ]) ?>
            <?php endif ?>

        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title pull-left"><?=Yii::_t('users.balance')?></h3>
          <div class="clearfix"></div>
        </div>
        <div class="panel-body">
          <?= DetailView::widget([
              'model' => [
                  'username' => $user['username'],
                  'balance_main' => Yii::$app->getFormatter()->asPrice($balance->getMain(), $balance->currency),
                  'balance_hold' => Yii::$app->getFormatter()->asPrice($balance->getHold(), $balance->currency),
                // tricky: Закоменчено в рамках mcms-1633
                  //'payment_next_date' => Yii::$app->getFormatter()->asDate($paymentNextDate, 'long'),
                  'summary_to_payment' => Yii::$app->getFormatter()->asPrice($summaryToPayment, $balance->currency),
              ],
              'attributes' => [
                  [
                      'attribute' => 'username',
                      'label' => Yii::_t('users.user'),
                  ],
                  [
                    'format' => 'raw',
                    'attribute' => 'balance_main',
                    'label' => Yii::_t('users.balance-main'),
                  ],
                  [
                    'format' => 'raw',
                    'attribute' => 'balance_hold',
                    'label' => Yii::_t('users.balance-hold'),
                    'value' => function($row) use ($user) {
                      return Link::get('profit', ['id' => $user['id']],
                        ['class' => 'text-primary', 'data-pjax' => 0],
                        ArrayHelper::getValue($row, 'balance_hold')
                      );
                    }
                  ],/* tricky: Закоменчено в рамках mcms-1633
                  [
                      'attribute' => 'payment_next_date',
                      'label' => Yii::_t('users.payment-next-date'),
                  ],*/
              ]
          ]) ?>
        </div>
      </div>
    </div>
  </div>
<?php Pjax::end() ?>

<?php yii\bootstrap\Modal::begin(['id' => 'modal', 'options' => ['class' => 'clean-with-header']] ) ?>
  <div class="well">
    <?= Spinner::widget(['preset' => 'large']) ?>
  </div>
<?php yii\bootstrap\Modal::end() ?>
