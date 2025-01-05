<?php

namespace mcms\payments\components\mgmp\send;


/**
 * Interface MgmpSenderInterface
 * @package mcms\payments\components\reseller_profits\send
 */
interface MgmpSenderInterface
{
  /**
   * @param $paymentIds
   * @return array
   */
  public function requestStatuses($paymentIds);

  /**
   * Получаем инвойсы выплат реселлера
   * @param $updatedAtFrom
   * @return array
   */
  public function requestPaymentsInvoices($updatedAtFrom);

  /**
   * Получаем инвойсы
   * @param int $dateFrom
   * @return array
   */
  public function requestInvoices($dateFrom);

  /**
   * Получаем файл-квитанцию выплаты
   * @param int $paymentId
   * @return string
   */
  public function requestPaymentChequeFile($paymentId);

  /**
   * Получаем файл инвойса
   * @param $mgmpInvoiceId
   * @return string
   */
  public function requestInvoiceFile($mgmpInvoiceId);

  /**
   * @param $wallet
   * @return bool|mixed
   */
  public function checkWebmoney($wallet);

  /**
   * @param $ibanCode
   * @return bool|mixed
   */
  public function checkIbanCode($ibanCode);

  /**
   * Срок выполнения выплаты реселлеру
   * @return int|null|false
   * - int - период
   * - null - ограничение сроков выполнения выплаты отсутствуют
   * - false - не удалось определить
   */
  public function getResellerPayPeriod();

  /**
   * Крайний дата для выполнения выплаты
   * @return int|null
   */
  public function getResellerPayPeriodEndDate();

  /**
   * @param $cardNumber
   * @return bool|mixed
   */
  public function getExternalCard($cardNumber);

  /**
   * @param $wallet
   * @return bool|mixed
   */
  public function checkYandexWallet($wallet);

  /**
   * Получаем частичные оплаты
   * @param $paymentId
   * @return array|bool
   */
  public function getPaymentChunks($paymentId);
}