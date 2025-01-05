<?php

namespace mcms\payments\models;

use mcms\payments\lib\payprocess\builders\PayoutServiceBuilder;
use mcms\payments\lib\payprocess\components\StatusConverter;
use rgk\payprocess\components\handlers\AbstractPayHandler;
use rgk\payprocess\components\PayoutService;
use rgk\payprocess\components\serviceResponse\PayoutPayResponse;
use Yii;
use yii\base\Object;
use yii\db\Exception;

/**
 * Автовыплаты.
 * TRICKY Когда будут делаться массовые автовыплаты, нужно сверять логику с устаревшим классом mcms\payments\models\AutoPaymentsForm (см. историю git).
 * Так же важно не забыть о блэк листе mcms/payments/models/UserPayment.php:723.
 * Массовые автовыплаты лучше делать отдельным классом
 */
class AutoPayout extends Object
{
  /** @var UserPaymentForm */
  private $userPayment;
  /** @var array */
  private $_message = '';

  /**
   * @inheritdoc
   */
  public function __construct(UserPaymentForm $userPayment)
  {
    $this->userPayment = $userPayment;
    parent::__construct();
  }

  /**
   * Выполнить выплату
   * @return bool
   * @throws Exception
   */
  public function pay()
  {
    $this->userPayment->setScenario(UserPaymentForm::SCENARIO_AUTOPAY);
    $this->userPayment->processing_type = UserPaymentForm::PROCESSING_TYPE_API;

    if (!$this->userPayment->isAvailableAutopay()) return false;
    if (!$this->userPayment->setStatusToProcessApi()) return false;

    // Подготовка параметров
    $sender = PayoutServiceBuilder::getSender($this->userPayment);
    $receiver = PayoutServiceBuilder::getReceiver($this->userPayment);
    if (!$sender || !$receiver) {
      $this->userPayment->save();
      return false;
    }

    // Проведение выплаты
    /** @var PayoutService $payoutService */
    $payoutService = PayoutServiceBuilder::getPayoutService();
    $payoutResult = $payoutService->pay(
      $sender,
      $receiver,
      $this->userPayment->amount,
      $this->userPayment->currency,
      $this->userPayment->id,
      $this->userPayment->generateDescription(),
      $this->userPayment->autoPayComment
    );

    // Определение статуса обработки выплаты и обновление информации о выплате
    $this->userPayment->status = StatusConverter::payoutServiceToUserPayment($payoutResult->status);

    if ($this->userPayment->status === false) {
      Yii::error('Получен неизвестный статус "' . $payoutResult->status . '" выполнения выплаты', __METHOD__);
      $message = Yii::_t('payments.payout-info.undefined_status', ['status' => $payoutResult->status]);
      $this->setMessage($message);

      $this->userPayment->status = UserPaymentForm::STATUS_ERROR;
      $this->userPayment->error_info = $message;
    }

    // Обновление информации о выплате
    $result = false;
    switch ($payoutResult->status) {
      case AbstractPayHandler::STATUS_COMPLETED:
        $result = true;
        break;
      case AbstractPayHandler::STATUS_ERROR:
        // Выплата ставится в статус "Отложенные", что бы человек делающий выплату разобрался в причине
        $this->userPayment->status = UserPaymentForm::STATUS_ERROR;
        $this->userPayment->error_info = implode('; ', $payoutResult->getErrors(PayoutPayResponse::SERVICE_ERRORS));
        $this->setMessage($payoutResult->getFirstError(PayoutPayResponse::SERVICE_ERRORS));
        $result = false;
        break;
      case AbstractPayHandler::STATUS_PROCESS:
        $this->setMessage(Yii::_t('payments.payout-info.payout_in_process'));
        $result = true;
        break;
    }

    $this->userPayment->processed_by = Yii::$app->user->id;

    if (!$this->userPayment->save()) {
      Yii::error('Не удалось обновить выплату ' . $this->userPayment->id . '. Результат автовыплаты неизвестен');
      $this->setMessage(Yii::_t('payments.payout-info.send_process_but_cant_update'));
      return false;
    }

    return $result;
  }

  /**
   * @return array
   */
  public function getMessage()
  {
    return $this->_message;
  }

  /**
   * @param $message
   */
  private function setMessage($message)
  {
    $this->_message = $message;
  }
}