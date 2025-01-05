<?php

namespace mcms\payments\commands;

use Exception;
use mcms\payments\components\mgmp\send\ApiMgmpSender;
use mcms\payments\lib\mgmp\TypeCaster;
use mcms\payments\lib\payprocess\components\PayoutServiceProxy;
use mcms\payments\lib\payprocess\components\StatusConverter;
use mcms\payments\models\UserPaymentChunk;
use mcms\payments\models\UserPaymentForm;
use rgk\payprocess\components\serviceResponse\PayoutStatusResponse;
use Yii;
use yii\console\Controller;
use yii\helpers\FileHelper;

/**
 * Обновление статусов платежей из Api
 * Class UpdateStatusController
 * @package mcms\payments\lib\payprocess\commands
 */
class UpdatePaymentStatusController extends Controller
{
  public function actionApi()
  {
    // !!!
    // TRICKY тут используется UserPaymentForm потому что в нем переопределен метод save, который создает инвойсы
    $payments = UserPaymentForm::find()->where([
      'status' => UserPaymentForm::STATUS_PROCESS,
      'processing_type' => UserPaymentForm::PROCESSING_TYPE_API,
    ])->each();

    if (empty($payments)) return;

    /** @var UserPaymentForm $payment */
    foreach ($payments as $payment) {
      $this->stdout("Try update status \"{$payment->status}\" for payment \"{$payment->id}\"...\n");

      /** @var PayoutStatusResponse $serviceStatusInfo */
      $serviceStatusInfo = PayoutServiceProxy::getStatus($payment);
      if (!$serviceStatusInfo) {
        $this->stderr("Cant get payment status\n");
        continue;
      }

      $paymentStatus = StatusConverter::payoutServiceToUserPayment($serviceStatusInfo->status);
      if (!$paymentStatus) {
        $this->stderr("Unknown status {$serviceStatusInfo->status}\n");
        continue;
      }

      if ($paymentStatus === $payment->status) {
        $this->stdout("Status not changed\n");
        continue;
      }

      $payment->status = $paymentStatus;

      // Если статус неудача, то пихаем ответ в error_info. Иначе это просто сообщение из апи, кладём в поле response
      if ($paymentStatus == UserPaymentForm::STATUS_ERROR) {
        $payment->error_info = $serviceStatusInfo->message;
      } else {
        $payment->response = $serviceStatusInfo->message;
      }
      if ($payment->save()) {
        $this->stdout("Payment status updated to \"{$paymentStatus}\"\n");
      } else {
        $this->stderr("Can not update status. Reason: {$payment->getLastError()}\n");
      }
    }
  }

  /**
   * Обновление статусов внешних выплат
   */
  public function actionExternal()
  {
    $query = UserPaymentForm::find()->select('id')->where([
      'processing_type' => UserPaymentForm::PROCESSING_TYPE_EXTERNAL,
      'status' => [UserPaymentForm::STATUS_PROCESS, UserPaymentForm::STATUS_AWAITING, UserPaymentForm::STATUS_DELAYED],
    ]);

    $this->stdout('Fetch payments SQL: ' . $query->createCommand()->rawSql . "\n");

    $ids = $query->column();
    /**
     * @var ApiMgmpSender $sender
     */
    $sender = Yii::createObject('mcms\payments\components\mgmp\send\MgmpSenderInterface');
    $result = $sender->requestStatuses($ids);

    if (!empty($result['success'])) {
      // тут используется модель UserPaymentForm, потому что в ней переопределен метод save в котором создаются инвойсы
      $payments = UserPaymentForm::find()->where(['id' => array_keys($result['data'])])->each();

      /** @var UserPaymentForm $payment */
      foreach ($payments as $payment) {
        $mgmpPayment = &$result['data'][$payment->id];
        $status = $mgmpPayment['status'];
        $payment->status = TypeCaster::mgmp2mcms($status);

        // костыль, иначе ресовская выплата не сохранялась т.к. поле пустое
        $payment->from_date = $payment->from_date ?: date('Y-m-d');
        $payment->to_date = $payment->to_date ?: date('Y-m-d');

        $payment->response = $mgmpPayment['comment'];
        $payment->payed_at = $mgmpPayment['payed_at'] ?: null;
        $this->saveChequeFile($payment, $mgmpPayment, $sender);

        $this->refreshChunks($payment, $sender);

        if ($payment->status === $payment::STATUS_ERROR) {
          // TODO нужно сделать TypeCaster::mgmp2mcmsPartner и mgm2mcmsReseller
          // TRICKY для реселлера нужно сразу отменять выплату и возвращать баланс
          if ($status === TypeCaster::STATUS_CANCELED && UserPaymentForm::getResellerId() === $payment->user_id) {
            $payment->status = $payment::STATUS_CANCELED;
          }
          $payment->error_info = $mgmpPayment['comment'];
        }

        if ($payment->status === $payment->getOldAttribute('status')) {
          $this->stdout("Status not changed\n");
          continue;
        }

        if (!$payment->handleExternalProcess()) {
          $this->stderr('Cannot save payment. Validation error: ' . print_r($payment->getErrors(), 1));
        }
      }
    }
  }

  /**
   * @param UserPaymentForm $payment
   * @param $mgmpPayment
   * @param $sender
   * @return bool
   */
  private function saveChequeFile(UserPaymentForm $payment, $mgmpPayment, ApiMgmpSender $sender)
  {
    if (empty($mgmpPayment['check_file'])) {
      return null;
    }

    if ($payment->cheque_file === $mgmpPayment['check_file']) {
      $this->stdout("Cheque file was not changed\n");
      return null;
    }

    $chequeFile = $sender->requestPaymentChequeFile($payment->id);
    if (!$chequeFile) {
      $this->stderr("Cheque file content is wrong");
      return false;
    }

    // Приведение названия файла в формат указанный в \mcms\payments\models\UserPayment::behaviors
    // TRICKY В БД название файла сохраняется без ID
    $chequeFileName = $mgmpPayment['check_file'];
    $chequeFileName = explode('.', $chequeFileName);
    $chequeFileName[count($chequeFileName) - 2] .= '-' . $payment->id;
    $chequeFileName = implode('.', $chequeFileName);

    $chequeDirectoryPath = Yii::getAlias($payment->chequeDirectoryPath);
    $chequeFilePath = $chequeDirectoryPath . '/' . $chequeFileName;

    if (!file_exists($chequeDirectoryPath)) {
      FileHelper::createDirectory($chequeDirectoryPath);
    }

    if (file_exists($chequeFilePath)) {
      $this->stderr("File already exists \"{$chequeFilePath}\"\n");
      return false;
    }

    if (file_put_contents($chequeFilePath, $chequeFile) === false) {
      $this->stderr("Cannot save payment cheque \"{$chequeFilePath}\"\n");
      return false;
    } else {
      // Новый файл прописывается в модель только при успехе
      $payment->cheque_file = $mgmpPayment['check_file'];
    }

    return true;
  }

  /**
   * Обновим частичные выплаты. Для этого достаём из МП актуальные.
   * И если вернулся массив, то сперва удаляем всё что есть у нас и вставляем новые данные.
   *
   * @param UserPaymentForm $payment
   * @param ApiMgmpSender $sender
   */
  private function refreshChunks(UserPaymentForm $payment, ApiMgmpSender $sender)
  {
    $chunks = $sender->getPaymentChunks($payment->id);
    if ($chunks === false) {
      $this->stderr("Chunks result is wrong");
      return;
    }

    $transaction = Yii::$app->db->beginTransaction();

    try {

      UserPaymentChunk::deleteAll(['payment_id' => $payment->id]);

      if (empty($chunks)) {
        $transaction->commit();
        return;
      }

      $batchInsert = array_map(function($chunk) use ($payment) {
        return [$payment->id, $chunk['id'], $chunk['amount'], $chunk['created_at'], $chunk['updated_at']];
      }, $chunks);

      if (!empty($batchInsert)) {
        $successCount = Yii::$app->db->createCommand()
          ->batchInsert(
            UserPaymentChunk::tableName(),
            ['payment_id', 'external_id', 'amount', 'created_at', 'updated_at'],
            $batchInsert
          )->execute();

        if ($successCount !== count($chunks)) {
          $this->stderr("Not all chunks successfully inserted ($successCount/" . count($chunks) . ")");
          $transaction->rollBack();
          return;
        }
      }
      $transaction->commit();
    } catch (Exception $e) {
      Yii::error('Refresh chunks exception: ' . $e->getMessage(), __METHOD__);
      $transaction->rollBack();
      return;
    }
  }
}