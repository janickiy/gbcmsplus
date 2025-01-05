<?php

namespace mcms\payments\lib\payprocess\components;

use mcms\payments\lib\payprocess\builders\PayoutServiceBuilder;
use mcms\payments\lib\payprocess\models\PayoutInfoError;
use mcms\payments\lib\payprocess\models\PaypalPayoutInfo;
use mcms\payments\lib\payprocess\models\WebMoneyPayoutInfo;
use mcms\payments\lib\payprocess\models\YandexMoneyPayoutInfo;
use mcms\payments\models\paysystems\PaySystemApi;
use mcms\payments\models\UserPayment;
use mcms\payments\models\wallet\Wallet;
use rgk\payprocess\components\PayoutService;
use rgk\payprocess\components\serviceResponse\PayoutStatusResponse;
use yii\base\Component;
use yii\web\NotFoundHttpException;

/**
 * Прокси для работы с PayoutService
 * Class PayoutServiceProxy
 */
class PayoutServiceProxy extends Component
{
  /**
   * Получение статуса выплаты из PayoutService
   * @param UserPayment $model
   * @return PayoutStatusResponse|null
   */
  public static function getStatus(UserPayment $model)
  {
    /**
     * @var PayoutService $payoutService
     */
    $payoutService = PayoutServiceBuilder::getPayoutService();
    $sender = PayoutServiceBuilder::getSender($model);
    $receiver = PayoutServiceBuilder::getReceiver($model);

    return is_array($sender) && is_array($receiver) ? $payoutService->getPayoutStatus($sender, $receiver, $model->id) : null;
  }

  /**
   * Модель для отображения DetailView
   * @param $id
   * @return PayoutInfoError|WebMoneyPayoutInfo|YandexMoneyPayoutInfo
   * @throws NotFoundHttpException
   */
  public static function getPayoutInfo($id)
  {
    if (($model = UserPayment::findOne($id)) === null) {
      throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @var PayoutService $payoutService
     */
    $payoutService = PayoutServiceBuilder::getPayoutService();
    $sender = PayoutServiceBuilder::getSender($model);
    $receiver = PayoutServiceBuilder::getReceiver($model);

    if ($sender && $receiver) {
      $data = $payoutService->getPayoutDetails($sender, $receiver, $id);

      return self::getPayoutInfoModel($sender, $receiver, $data);
    }

    return new PayoutInfoError(['errorMessage' => 'can not display info for payment: ' . $id . '. Please contact administrator']);
  }

  /**
   *
   * @param $sender
   * @param $receiver
   * @param $data
   * @return PayoutInfoError|WebMoneyPayoutInfo|YandexMoneyPayoutInfo|PaypalPayoutInfo
   */
  private static function getPayoutInfoModel($sender, $receiver, $data)
  {
    switch ($sender['paysystem']) {
      case 'webmoney':
      case 'wmlight':
        return new WebMoneyPayoutInfo($data);
        break;
      case 'yandex-money':
        return new YandexMoneyPayoutInfo($data);
        break;
      case 'paypal':
        return new PaypalPayoutInfo($data);
        break;
      default:
        return new PayoutInfoError(['errorMessage' => 'Undefined paysystem. Please contact administrator']);
    }
  }

  public static function getBalance($id)
  {
    $api = PaySystemApi::findOne($id);
    $payoutService = PayoutServiceBuilder::getPayoutService();
    return $payoutService->getBalanсe([
      'paysystem' => $api->code,
      'params' => $api->getSettingsAsArray(),
    ], $api->currency);
  }
}