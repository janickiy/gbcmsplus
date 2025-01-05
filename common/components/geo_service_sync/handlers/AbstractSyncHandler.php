<?php

namespace common\components\geo_service_sync\handlers;

use mcms\common\traits\LogTrait;
use rgk\geoservice_client\Configuration;
use rgk\geoservice_client\endpoints\AbstractEndpoint;
use yii\httpclient\Client;
use yii\httpclient\RequestEvent;

abstract class AbstractSyncHandler
{
    use LogTrait;

    /** @var Configuration */
    protected $configuration;
    /** @var Client */
    protected $httpClient;

    /**
     * AbstractSyncHandler constructor.
     * @param Configuration $configuration
     * @param Client $client
     */
    public function __construct(Configuration $configuration, Client $client)
    {
        $client->on(Client::EVENT_AFTER_SEND, function (RequestEvent $event) {
            $this->log(' [geo service client] Request url: ' . $event->request->url . PHP_EOL);
            $this->log(' [geo service client] Response status code: ' . $event->response->statusCode . PHP_EOL);
//      $this->log(' [geo service client] Response: ' . $event->response->content . PHP_EOL);
        });

        $this->configuration = $configuration;
        $this->httpClient = $client;
    }

    /**
     * @param $endpointClass
     * @return AbstractEndpoint
     */
    public function getEndpoint($endpointClass)
    {
        return new $endpointClass($this->configuration, $this->httpClient);
    }

    abstract public function sync();

    public function __invoke()
    {
        $this->sync();
    }
}
