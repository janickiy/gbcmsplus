<?php
namespace mcms\payments\components\rbac;

use mcms\payments\models\UserPayment;
use Yii;
use yii\rbac\Rule;


class ViewPaymentRule extends Rule
{
  public $name = 'PaymentsViewPaymentRule';
  public $description = 'Can view payments';

  public function execute($user, $item, $params)
  {
    /** @var UserPayment $model */
    $model = $params['payment'];

    if (Yii::$app->user->can('canProcessAllPayments') === false) {
      return $model->isAwaiting() || $model->processed_by === Yii::$app->user->id;
    }

    return
      Yii::$app->user->can('canViewAllPayments') ||
      $model->user_id == $user ||
      (
        Yii::$app->user->can('canViewPartnerPayments') &&
        array_key_exists('partner', Yii::$app->authManager->getRolesByUser($model->user_id)) &&
        !!$model->getInvoicesPaymentAndEarlyPayment()->count()
      )
    ;
  }
}