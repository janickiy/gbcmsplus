<?php

namespace mcms\common\helpers;

use RuntimeException;
use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;

/**
 * Class HttpAdapter
 * @package common\components\provider\mobleaders
 */
class HttpAdapter extends BaseObject
{
    public $options = [];

    public $headers = [];

    public $method = 'post';

    public $format = Client::FORMAT_JSON;

    /**
     * @param string $url
     * @param array $data
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function getFormattedResponse(string $url, array $data = [])
    {
        $response = $this->getResponse($url, $data);

        if ($this->format !== Client::FORMAT_JSON) {
            return $response->content;
        }

        $response->setFormat($this->format);

        return $response->data;
    }

    /**
     * @param string $url
     * @param array $data
     * @param int $timeout
     * @return null
     * @throws \yii\base\InvalidConfigException
     */
    public function getResponse(string $url, array $data = [], int $timeout = 5)
    {
        $client = Yii::createObject(Client::class);
        $client->setTransport(CurlTransport::class);

        $options = ArrayHelper::merge([
            'timeout' => $timeout,
        ], $this->options);

        $request = $client->createRequest()
            ->setMethod($this->method)
            ->setUrl($url)
            ->setHeaders($this->headers)
            ->setOptions($options);

        $data && $request->setData($data);

        try {
            $response = $request->send();
        } catch (\Exception $e) {
            return null;
        }

        if (!$response) {
            throw new RuntimeException('No response');
        }

        if (!$response->isOk) {
            throw new RuntimeException(
                sprintf(
                    "Wrong response, code=%s. Response data: %s",
                    $response->statusCode,
                    $response->content
                )
            );
        }

        return $response;
    }
}
