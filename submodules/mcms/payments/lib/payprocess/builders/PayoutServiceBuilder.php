<?php
namespace mcms\payments\lib\payprocess\builders;

use mcms\payments\models\UserPayment;
use rgk\payprocess\components\PayoutService;
use Yii;

/**
 * Class PayoutServiceBuilder
 * @package mcms\payments\lib\payprocess\builders
 */
class PayoutServiceBuilder
{
  /**
   * @return PayoutService|Object
   * @throws \yii\base\InvalidConfigException
   */
  public static function getPayoutService()
  {
    return Yii::createObject([
      'class' => PayoutService::class,
      'projectId' => Yii::$app->getModule('payments')->getProjectId(),
    ]);
  }

  public static function getSender(UserPayment $model)
  {
    // Определение отправителя
    $sender = $model->walletModel->getSender($model->currency);
    if (!$sender || !$sender->isActive()) return false;

    return [
      'paysystem' => $sender->code,
      'params' => $sender->getSettingsAsArray(),
    ];
  }

  public static function getReceiver(UserPayment $model)
  {
    // Определение получателя
    $receiver = $model->getWallet()->getAccountAssoc();
    if (empty($receiver)) return false;

    return [
      'paysystem' => $model->walletModel->code,
      'params' => $receiver,
    ];
  }
}
