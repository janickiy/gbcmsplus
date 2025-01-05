<?php
namespace mcms\payments\components\rbac;

use mcms\payments\models\UserPayment;
use Yii;
use yii\rbac\Rule;


class UpdatePaymentRule extends Rule
{
  public $name = 'PaymentsUpdatePaymentRule';
  public $description = 'Can update payments';

  public function execute($user, $item, $params)
  {
    /** @var UserPayment $model */
    $model = $params['payment'];

    if (Yii::$app->user->can('canProcessAllPayments') === false) {
      return $model->isAwaiting() || $model->processed_by === Yii::$app->user->id;
    }

    return
      Yii::$app->user->can('canEditAllPayments') ||
      (
        Yii::$app->user->can('canEditPartnerPayments') &&
        array_key_exists('partner', Yii::$app->authManager->getRolesByUser($model->user_id)) &&
        !$model->isReadonly()
      )
    ;
  }
}