<?php
namespace mcms\payments\components\rbac;

use mcms\payments\models\UserPayment;
use mcms\payments\models\wallet\Wallet;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rbac\Rule;


class AutoPayoutRule extends Rule
{
  public $name = 'PaymentsAutoPayoutRule';
  public $description = 'Can auto payout to wallet';

  public function execute($user, $item, $params)
  {
    /** @var UserPayment $model */
    $model = $params['payment'];
    if (!$model->isPayable()) {
      return false;
    }

    if (Yii::$app->user->can('canProcessAllPayments') === false) {
      return $model->isAwaiting() || $model->processed_by === Yii::$app->user->id;
    }

    return Yii::$app->user->can('canEditAllPayments') || Yii::$app->user->can('canEditPartnerPayments');
  }
}