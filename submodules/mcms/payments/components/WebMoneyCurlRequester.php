<?php

namespace mcms\payments\components;


use baibaratsky\WebMoney\Exception\RequesterException;
use baibaratsky\WebMoney\Request\AbstractRequest;
use baibaratsky\WebMoney\Request\Requester\CurlRequester;
use baibaratsky\WebMoney\Request\XmlRequest;
use baibaratsky\WebMoney\Api\X\Request;
use mcms\common\helpers\curl\Curl;
use ReflectionClass;
use Yii;

class WebMoneyCurlRequester extends CurlRequester
{
  /**
   * @inheritDoc
   */
  protected function request(AbstractRequest $request)
  {
    if (!$request instanceof XmlRequest) {
      throw new RequesterException('This requester doesn\'t support such type of request.');
    }

    $curlParams = [
      'url' => $request->getUrl(),
      'isPost' => true,
      'postFields' => $request->getData(),
      'verifyCertificate' => $this->verifyCertificate,
      'sslVersion' => 1
    ];

    if ($this->verifyCertificate) {
      $filePath = (new \ReflectionClass(CurlRequester::class))->getFileName();
      $curlParams['verifyCertificatePath'] = dirname(dirname(dirname($filePath))) . '/WMUsedRootCAs.cer';
    }


    if ($request instanceof Request && $request->getAuthType() === Request::AUTH_LIGHT) {
      $curlParams['sslCert'] = $request->getLightCertificate();
      $curlParams['sslKey'] = $request->getLightKey();
    }

    return (new Curl($curlParams))->getResult();
  }

}