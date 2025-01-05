<?php

namespace mcms\payments\components\mgmp\send;

use mcms\common\mgmp\MgmpClient;
use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;


/**
 * class ApiMgmpSender
 * @package mcms\payments\components\reseller_profits\send
 */
class ApiMgmpSender extends Object implements MgmpSenderInterface
{
  /** @const string */
  const RESELLER_PAY_PERIOD_CACHE_KEY = 86400;
  /** @const int */
  const RESELLER_PAY_PERIOD_CACHE_DURATION = 86400;

  /**
   * @inheritdoc
   */
  public function requestStatuses($paymentIds)
  {
    Yii::info('Sending...');
    $mgmpResponse = Yii::$app->mgmpClient->requestData(MgmpClient::URL_PAY_STATUS, ['payments' => $paymentIds]);

    if (!$mgmpResponse->getIsOk()) {
      Yii::error('MGMP response http status=' . $mgmpResponse->statusCode);
      return false;
    }

    $data = $mgmpResponse->getData();
    Yii::info('Got result: ' . Json::encode($data));
    return $data;
  }

  /**
   * @inheritdoc
   */
  public function requestPaymentChequeFile($paymentId)
  {
    $mgmpResponse = Yii::$app->mgmpClient->requestData(MgmpClient::URL_GET_PAYMENT_CHECK_FILE, [
      'paymentId' => $paymentId
    ]);

    return $mgmpResponse->getContent();
  }

  /**
   * @inheritdoc
   */
  public function requestPaymentsInvoices($updatedAtFrom)
  {
    Yii::info('Sending...');
    $mgmpResponse = Yii::$app->mgmpClient->requestData(MgmpClient::URL_GET_RESELLER_PAYMENTS_INVOICES, [
      'updatedAtFrom' => $updatedAtFrom
    ]);

    if (!$mgmpResponse->getIsOk()) {
      Yii::error('MGMP response http status=' . $mgmpResponse->statusCode);
      return false;
    }

    $data = $mgmpResponse->getData();
    Yii::info('Got result: ' . Json::encode($data));
    return $data;
  }

  /**
   * @inheritdoc
   */
  public function requestInvoices($dateFrom)
  {
    $mgmpResponse = Yii::$app->mgmpClient->requestData(MgmpClient::URL_GET_INVOICES, [
      'dateFrom' => $dateFrom
    ]);

    if (!$mgmpResponse->getIsOk()) {
      Yii::error('Не удалось получить инвойсы для импортирования. MGMP response http status=' . $mgmpResponse->statusCode);
      return false;
    }

    return $mgmpResponse->getData();
  }

  /**
   * @inheritdoc
   */
  public function requestInvoiceFile($mgmpInvoiceId)
  {
    $mgmpResponse = Yii::$app->mgmpClient->requestData(MgmpClient::URL_GET_INVOICE_FILE, [
      'invoiceId' => $mgmpInvoiceId
    ]);

    return $mgmpResponse->getContent();
  }


  /**
   * @inheritdoc
   */
  public function checkWebmoney($wallet)
  {
    $mgmpResponse = Yii::$app->mgmpClient->requestData(MgmpClient::URL_CHECK_WEBMONEY, ['wallet' => urlencode($wallet)]);

    if (!$mgmpResponse->getIsOk()) {
      Yii::error('MGMP response http status=' . $mgmpResponse->statusCode);
      return false;
    }

    $arrResult = $mgmpResponse->getData();
    if (ArrayHelper::getValue($arrResult, 'success', false)) {
      return $arrResult['data'];
    }

    return false;
  }

  /**
   * @inheritdoc
   */
  public function checkIbanCode($ibanCode)
  {
    $mgmpResponse = Yii::$app->mgmpClient->requestData(MgmpClient::URL_CHECK_IBAN, ['ibanCode' => urlencode($ibanCode)]);

    if (!$mgmpResponse->getIsOk()) {
      Yii::error('MGMP response http status=' . $mgmpResponse->statusCode);
      return false;
    }

    $arrResult = $mgmpResponse->getData();
    if (ArrayHelper::getValue($arrResult, 'success', false)) {
      return $arrResult['data'];
    }

    return false;
  }

  /**
   * @inheritdoc
   */
  public function getExternalCard($cardNumber)
  {
    $mgmpResponse = Yii::$app->mgmpClient->requestData(MgmpClient::URL_GET_EXTERNAL_CARD, [
      'cardNumber' => urlencode($cardNumber)
    ]);

    if (!$mgmpResponse->getIsOk()) {
      Yii::error('MGMP response http status=' . $mgmpResponse->statusCode);
      return false;
    }

    $arrResult = $mgmpResponse->getData();
    if (ArrayHelper::getValue($arrResult, 'success', false)) {
      return $arrResult['data'];
    }

    return false;
  }

  /**
   * @inheritdoc
   */
  public function checkYandexWallet($wallet)
  {
    $mgmpResponse = Yii::$app->mgmpClient->requestData(MgmpClient::URL_CHECK_YANDEX, ['wallet' => urlencode($wallet)]);

    if (!$mgmpResponse->getIsOk()) {
      Yii::error('MGMP response http status=' . $mgmpResponse->statusCode);
      return false;
    }

    $arrResult = $mgmpResponse->getData();
    if (ArrayHelper::getValue($arrResult, 'success', false)) {
      return $arrResult['data'];
    }

    return false;
  }

  /**
   * @inheritdoc
   */
  public function getResellerPayPeriod()
  {
    $cache = Yii::$app->cache;
    $period = $cache->get('api_mgmp_sender_reseller_pay_period');
    if ($period === null || is_integer($period)) return $period;

    $response = Yii::$app->mgmpClient->requestData(MgmpClient::URL_RESELLER_PAY_PERIOD);

    if (!$response->getIsOk()) return false;

    $result = $response->getData();

    $period = ArrayHelper::getValue($result, 'data.pay_period', false);
    $cache->set(self::RESELLER_PAY_PERIOD_CACHE_KEY, $period, self::RESELLER_PAY_PERIOD_CACHE_DURATION);

    return $period;
  }

  /**
   * @inheritdoc
   */
  public function getResellerPayPeriodEndDate()
  {
    $period = $this->getResellerPayPeriod();
    return $period ? time() + $period * 86400 : null;
  }

  /**
   * @inheritdoc
   */
  public function getPaymentChunks($paymentId)
  {
    $mgmpResponse = Yii::$app->mgmpClient->requestData(MgmpClient::URL_PAYMENT_CHUNKS, ['paymentId' => $paymentId]);

    if (!$mgmpResponse->getIsOk()) {
      Yii::error('MGMP response http status=' . $mgmpResponse->statusCode);
      return false;
    }

    $arrResult = $mgmpResponse->getData();
    if (ArrayHelper::getValue($arrResult, 'success', false)) {
      $data = ArrayHelper::getValue($arrResult, 'data');

      if (is_array($data)) {
        return $data;
      }

      Yii::error('MGMP response success=true, but data is not array or empty:' . PHP_EOL . print_r($arrResult['data'], true));

      return false;
    }
    Yii::error('MGMP response success=false. Data:' . PHP_EOL . print_r($arrResult['data'], true));
    return false;
  }
}