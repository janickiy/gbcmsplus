<?php

namespace mcms\payments\commands;

use mcms\payments\lib\payprocess\components\PayoutServiceProxy;
use mcms\payments\models\UserPayment;
use rgk\payprocess\components\serviceResponse\PayoutStatusResponse;
use yii\console\Controller;

/**
 * Операции получение информации о выплатах
 *
 * Class PaymentsController
 * @package mcms\payments\commands
 */
class PaymentsController extends Controller
{

  /**
   * Получение информации по выплате из АПИ.
   * Для дебага, принтует класс @see \rgk\payprocess\components\serviceResponse\PayoutStatusResponse
   * @param $paymentId
   */
  public function actionGetApiPaymentInfo($paymentId)
  {
    /** @var UserPayment $payment */
    $payment = UserPayment::find()->where(['id' => (int)$paymentId])->one();

    if (empty($payment)) {
      $this->stderr("No such payment.\n");
      return;
    }

    if ($payment->processing_type != UserPayment::PROCESSING_TYPE_API) {
      $this->stderr("Process type of payment is not API.\n");
      return;
    }

    /** @var PayoutStatusResponse $serviceStatusInfo */
    $serviceStatusInfo = PayoutServiceProxy::getStatus($payment);
    $this->stdout("Wallet {$payment->walletModel->code}\n");

    $this->stdout(print_r($serviceStatusInfo, false));
  }

  /**
   * @param $id
   */
  public function actionInvoice($id)
  {
    $model = UserPayment::findOne($id);

    if (!$model->generateInvoiceFile()) {
      echo "Не удалось\n";
      return;
    }

    echo "Удалось\n";
  }
}
