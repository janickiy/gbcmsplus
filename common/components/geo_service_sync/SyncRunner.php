<?php

namespace common\components\geo_service_sync;

use common\components\geo_service_sync\handlers\CountriesSyncHandler;
use common\components\geo_service_sync\handlers\OperatorsSyncHandler;
use rgk\geoservice_client\Configuration;
use Yii;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;

class SyncRunner extends BaseObject
{
    /** @var Configuration */
    private $configuration;
    /** @var Client */
    private $client;

    public $syncHandlers = [
        CountriesSyncHandler::class,
        OperatorsSyncHandler::class,
    ];

    public function init()
    {
        parent::init();

        $url = ArrayHelper::getValue(Yii::$app->params, ['geo_client', 'url']);
        $token = ArrayHelper::getValue(Yii::$app->params, ['geo_client', 'token']);

        if (!$url || !$token) {
            throw new Exception('Can not instantiate Geo Service Configuration');
        }

        $this->configuration = new Configuration($url, $token);
        $this->client = new Client();
    }

    public function run()
    {
        foreach ($this->syncHandlers as $handler) {
            $handlerInstance = new $handler($this->configuration, $this->client);
            $handlerInstance();
        }
    }
}
