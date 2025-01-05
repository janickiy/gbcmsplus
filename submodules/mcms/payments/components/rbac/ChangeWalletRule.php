<?php
namespace mcms\payments\components\rbac;

use mcms\common\helpers\ArrayHelper;
use mcms\payments\models\UserPayment;
use mcms\payments\models\UserPaymentSetting;
use mcms\payments\models\UserWallet;
use Yii;
use yii\rbac\Rule;

class ChangeWalletRule extends Rule
{
  public $name = 'ChangeWalletRule';
  public $description = 'Can change wallet';

  public function execute($user, $item, $params)
  {
    if (Yii::$app->user->can('PaymentsCanChangeWalletForce')) {
      return true;
    }

    $userId = ArrayHelper::getValue($params, 'userId');

    if (Yii::$app->user->id == $userId) {
      return true;
    }

    return false;
  }
}