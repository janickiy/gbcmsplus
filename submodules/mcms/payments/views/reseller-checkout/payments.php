<?php
use mcms\common\widget\modal\Modal;
use mcms\payments\models\search\UserPaymentSearch;
use mcms\statistic\components\widgets\Totals;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var \yii\data\ActiveDataProvider $dataProvider */
/** @var bool $canCreate */
/** @var UserPaymentSearch $searchModel */

$this->beginBlock('actions');
echo Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'span',
    'class' => 'btn btn-success',
    'label' => Html::icon('credit-card') . ' ' . Yii::_t('payments.users.conversion')
  ],
  'url' => ['reseller-invoices/convert-modal'],
]);

echo Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'span',
    'class' => 'btn btn-success',
    'label' => Html::icon('usd') . ' ' . Yii::_t('payments.users.payments-order-payment')
  ],
  'url' => ['reseller-checkout/create'],
]);
$this->endBlock();
?>


<?php
Pjax::begin(['id' => 'reseller-payments']);
?>
<?= Totals::widget(['viewPath' => '/reseller-checkout/_totals']); ?>
<?= $this->render('_log', [
  'logDataProvider' => $dataProvider,
  'canCreate' => $canCreate,
  'searchModel' => $searchModel
]); ?>
<?php // todo удалить этот виджет вообще ResellerBalance::widget() ?>
<?php Pjax::end(); ?>