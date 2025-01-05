<?php

namespace mcms\common\mgmp;


use mcms\payments\Module;
use Yii;
use yii\base\Object;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use yii\httpclient\Response;

/**
 * Настройку в конфиг:
 * 'mgmpClient' => [
 *     'class' => \mcms\common\mgmp\MgmpClient::class
 *   ]
 *
 * И потом использовать так:
 * Yii::$app->mgmpClient->requestData(MgmpClient::URL_RESELLER_HOLD_SETTINGS);
 *
 *
 * Class MgmpClient
 * @package mcms\common\mgmp
 */
class MgmpClient extends Object
{

    const URL_RESELLER_HOLD_SETTINGS = '/api/v1/hold/hold-rules/reseller/';
    const URL_PAY_STATUS = '/api/v1/payments/resellers-payment-status/index/';
    const URL_CHECK_WEBMONEY = '/api/v1/payments/paysystem-check/webmoney/';
    const URL_CHECK_IBAN = '/api/v1/payments/paysystem-check/iban/';
    const URL_GET_EXTERNAL_CARD = '/api/v1/payments/paysystem-check/get-external-card/';
    const URL_CHECK_YANDEX = '/api/v1/payments/paysystem-check/yandex-wallet/';
    const URL_RESELLER_PAY_PERIOD = '/api/v1/payments/reseller-payments/get-pay-period/';
    /** @const string Инвойсы выплат реселлера */
    const URL_GET_RESELLER_PAYMENTS_INVOICES = '/api/v1/payments/reseller-invoices/index/';
    /** @const string Получение инвойсов без привязки к выплатам */
    const URL_GET_INVOICES = '/api/v1/payments/invoices/export/';
    const URL_GET_INVOICE_FILE = '/api/v1/payments/reseller-invoices/download-file/';
    const URL_GET_PAYMENT_CHECK_FILE = '/api/v1/payments/reseller-payments/download-check-file/';
    const URL_GET_CREDIT_SETTINGS = '/api/v1/credits/settings/get/';
    const URL_GET_CREDITS = '/api/v1/credits/credits/index/';
    const URL_PAYMENT_CHUNKS = '/api/v1/payments/payment-chunks/index/';
    const URL_RESELLER_PROFIT_IMPORT = '/api/v1/statistic/reseller-profit/import/';

    private $resellerId;
    private $secretKey;
    private $mgmpUrl;

    public function init()
    {
        parent::init();
        /** @var  Module $payModule */
        $payModule = Yii::$app->getModule('payments');
        $this->resellerId = $payModule->getMgmpResellerId();
        $this->secretKey = $payModule->getMgmpSecretKey();
        $this->mgmpUrl = $payModule->getMgmpUrl();
    }


    /**
     * @param $url
     * @param array $params
     * @param bool $isPost
     * @return Response
     */
    public function requestData($url, $params = [], $isPost = false)
    {
        return (new Client(['transport' => CurlTransport::class]))
            ->createRequest()
            ->setMethod($isPost ? 'post' : 'get')
            ->setUrl($this->prepareUrl($url))
            ->setData($this->prepareParams($params, true))
            ->send();
    }

    /**
     * Подготавливаем URI параметры запроса
     * @param array $params
     * @param bool $asArray
     * @return array|string
     */
    private function prepareParams($params = [], $asArray = false)
    {
        $resellerId = $this->resellerId;
        $time = time();
        $token = md5($this->secretKey . $time);

        $authParams = ['access_token' => $token, 'time' => $time, 'resellerId' => $resellerId];
        $params = array_merge($params, $authParams);

        return $asArray ? $params : http_build_query($params);
    }


    /**
     * @param $url
     * @return string
     */
    private function prepareUrl($url)
    {
        return rtrim($this->mgmpUrl, '/') . '/' . ltrim($url, '/');
    }
}