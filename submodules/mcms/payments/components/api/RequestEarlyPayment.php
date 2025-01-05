<?php

namespace mcms\payments\components\api;

use mcms\common\module\api\ApiResult;
use mcms\payments\components\AvailableCurrencies;
use mcms\payments\components\UserBalance as UserBalanceComponents;
use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserPayment;
use mcms\payments\models\UserPaymentSetting;
use mcms\payments\models\UserWallet;
use Yii;
use yii\helpers\ArrayHelper;

class RequestEarlyPayment extends ApiResult
{

  private $userId;
  private $paymentRequests;
  private $currency;

  function init($params = [])
  {
    $this->currency = ArrayHelper::getValue($params, 'currency');

    if (!$this->userId = ArrayHelper::getValue($params, 'userId')) {
      $this->addError('userId required');
    }

    if (!$this->paymentRequests = ArrayHelper::getValue($params, 'paymentRequests')) {
      $this->addError('paymentRequests required');
    }
  }

  public function getResult()
  {
    if ($this->getErrors()) {
      return false;
    }

    $time = time();
    /** @var UserPaymentSetting $userSettings */
    $userSettings = UserPaymentSetting::findOne(['user_id' => $this->userId]);
    $balance = new UserBalanceComponents($this->currency
      ? ['userId' => $this->userId, 'currency' => $this->currency]
      : ['userId' => $this->userId]
    );

    // TRICKY Не переносить эту проверку в цикл и не убирать false из getMain(), иначе можно будет при балансе например в 10 000 рублей снять 9 999 рублей бесконечное количество раз, так как выплата всегда будет проходить проверку на величину баланса
    $paymentAmountSum = array_sum(ArrayHelper::getColumn($this->paymentRequests, 'invoice_amount'));
    if ($balance->getMain(false) - $paymentAmountSum < 0) {
      $this->addError('insufficient funds');
      return false;
    }

    $transaction = Yii::$app->db->beginTransaction();
    try {
      foreach ($this->paymentRequests as $paymentRequest) {

        $availableCurrencies = (new AvailableCurrencies($this->userId))->getCurrencies();

        /** @var UserWallet $userWallet */
        $userWallet = UserWallet::findOne([
          'id' => $paymentRequest['user_wallet_id'],
          'user_id' => $this->userId,
          'currency' => $availableCurrencies
        ]);

        if (!$userWallet) {
          $this->addError('user wallet not found');
          $transaction->rollBack();
          return false;
        }

        $payment = new UserPayment([
          'user_id' => $this->userId,
          'user_wallet_id' => $userWallet->id,
          'wallet_type' => $userWallet->wallet_type,
          'invoice_amount' => $paymentRequest['invoice_amount'],
          'status' => UserPayment::STATUS_AWAITING,
          'created_at' => $time,
          'from_date' => Yii::$app->formatter->asGridDate($time),
          'to_date' => Yii::$app->formatter->asGridDate($time),
          'invoiceType' => UserBalanceInvoice::TYPE_EARLY_PAYMENT,
        ]);

        if (!$userSettings->canRequestPayments($userWallet)) {
          $this->addError('user payment system wallet not exists or payments is disabled');
          return false;
        }

        $payment->scenario = $payment::SCENARIO_CREATE;
        $payment->loadDefaultValues();

        $invoice = new UserBalanceInvoice([
          'user_id' => $this->userId,
          'currency' => $balance->getCurrency(),
          'amount' => -1 * $paymentRequest['invoice_amount'],
          'created_at' => $time,
          'type' => UserBalanceInvoice::TYPE_EARLY_PAYMENT
        ]);

        if (!$payment->save()) {
          $this->addError('payments model save error');
          $transaction->rollBack();
          return false;
        }
        $invoice->user_payment_id = $payment->id;

        if (!$invoice->save()) {
          $this->addError('invoice model save error');
          $transaction->rollBack();
          return false;
        }
      }

      $transaction->commit();
    } catch (\Exception $e) {
      $transaction->rollBack();
      $this->addError($e->getMessage());
      return false;
    }

    return true;
  }
}