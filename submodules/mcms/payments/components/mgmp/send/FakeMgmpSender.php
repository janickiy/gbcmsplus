<?php

namespace mcms\payments\components\mgmp\send;

use yii\base\Object;


/**
 * class FakeMgmpSender
 * @package mcms\payments\components\reseller_profits\send
 */
class FakeMgmpSender extends Object implements MgmpSenderInterface
{

  /**
   * @return int
   */
  public function getResellerPayPeriod()
  {
    // FAKE IT TOO!
    return 10;
  }

  /**
   * @return int
   */
  public function getResellerPayPeriodEndDate()
  {
    return time() + $this->getResellerPayPeriod() * 86400;
  }

  /**
   * @param $paymentIds
   * @return array
   */
  public function requestStatuses($paymentIds)
  {
    return [];
  }

  /**
   * @param $wallet
   * @return bool|mixed
   */
  public function checkWebmoney($wallet)
  {
    return true;
  }

  /**
   * @param $ibanCode
   * @return bool|mixed
   */
  public function checkIbanCode($ibanCode)
  {
    return true;
  }

  /**
   * @param $cardNumber
   * @return bool|mixed
   */
  public function getExternalCard($cardNumber)
  {
    return true;
  }

  /**
   * @param $wallet
   * @return bool|mixed
   */
  public function checkYandexWallet($wallet)
  {
    return true;
  }

  /**
   * Получаем инвойсы
   * @param $updatedAtFrom
   * @return array
   */
  public function requestPaymentsInvoices($updatedAtFrom)
  {
    return [];
  }

  /**
   * Получаем файл инвойса
   * @param $mgmpInvoiceId
   * @return string
   */
  public function requestInvoiceFile($mgmpInvoiceId)
  {
    return '';
  }

  /**
   * Получаем файл-квитанцию выплаты
   * @param int $paymentId
   * @return string
   */
  public function requestPaymentChequeFile($paymentId)
  {
    return '';
  }

  /**
   * Получаем инвойсы
   * @param int $dateFrom
   * @return array
   */
  public function requestInvoices($dateFrom)
  {
    return [];
  }

  /**
   * Получаем частичные оплаты
   * @param $paymentId
   * @return array|bool
   */
  public function getPaymentChunks($paymentId)
  {
    return [];
  }
}
