<?php
namespace mcms\mcms\payments\models;

use mcms\payments\models\AutoPayout;
use mcms\payments\models\UserPayment;
use mcms\payments\models\UserPaymentForm;
use mcms\payments\models\wallet\Wallet;
use Yii;
use yii\base\Object;
use yii\db\ActiveQuery;

/**
 * Массовая автовыплата
 * TRICKY Временно захарадкожено только для вебмани, так как в автовыплатах на другие ПС пока нет уверенности
 */
class MassAutoPayout extends Object
{
  /** @var int Количество выплат для которых процесс автовыплаты успешно запущен */
  private $processCount;
  /** @var int Количество выплат для которых процесс автовыплаты должен быть запущен */
  private $totalCount;
  /** @var string[] Ошибки запуска автовыплаты */
  private $errors;

  /**
   * Выполнить массовую автовыплату
   * @return bool
   */
  public function execute()
  {
    $success = true;
    $this->totalCount = 0;
    $this->processCount = 0;
    $this->errors = [];

    $payments = $this->findAvailablePayments();
    /** @var UserPaymentForm $payment */
    foreach ($payments->each() as $payment) {
      $this->totalCount++;

      if ($this->payout($payment)) {
        $this->processCount++;
        continue;
      }

      $success = false;
    }

    return $success;
  }

  /**
   * Запустить автовыплату
   * @param UserPaymentForm $payment
   * @return bool
   */
  private function payout(UserPaymentForm $payment)
  {
    $autoPayout = new AutoPayout($payment);

    if ($autoPayout->pay()) return true;

    $error = Yii::_t('payments.user-payments.mass_payout_error', ['paymentId' => $payment->id]);
    if ($payment->getLastError()) $error .= '. ' . $payment->getLastError();
    if ($autoPayout->getMessage()) $error .= '. ' . $autoPayout->getMessage();
    $this->errors[] = $error;

    return false;
  }

  /**
   * @see errors
   * @return string[]
   */
  public function getErrors()
  {
    return $this->errors;
  }

  /**
   * @see processCount
   * @return int
   */
  public function getProcessCount()
  {
    return $this->processCount;
  }

  /**
   * @see totalCount
   * @return int
   */
  public function getTotalCount()
  {
    return $this->totalCount;
  }

  /**
   * Выплаты доступные для массового выполнения
   * @return ActiveQuery
   */
  public function findAvailablePayments()
  {
    // TRICKY Временное ограничение по вебмани
    $webmoney = Wallet::findOne(['code' => 'webmoney']);

    return UserPaymentForm::find()
      ->andWhere([
        'status' => UserPaymentForm::STATUS_AWAITING,
        'wallet_type' => $webmoney->id,
      ])
      ->andWhere(['<>', 'user_id', UserPayment::getResellerId()]);
  }
}