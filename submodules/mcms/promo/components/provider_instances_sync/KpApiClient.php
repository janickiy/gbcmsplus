<?php

namespace mcms\promo\components\provider_instances_sync;


use mcms\promo\components\provider_instances_sync\api_drivers\ApiDriverInterface;
use mcms\promo\components\provider_instances_sync\dto\Error;
use mcms\promo\components\provider_instances_sync\dto\Instance;
use mcms\promo\components\provider_instances_sync\dto\Stream;
use mcms\promo\components\provider_instances_sync\requests\AuthRequest;
use mcms\promo\components\provider_instances_sync\requests\CreateStreamRequest;
use Yii;

/**
 * Class ProviderInstancesApi
 */
class KpApiClient
{
  /** @var ApiDriverInterface */
  private $apiDriver;

  /**
   * @param ApiDriverInterface $apiDriver
   */
  public function __construct(ApiDriverInterface $apiDriver)
  {
    $this->setApiDriver($apiDriver);
  }

  /**
   * @param ApiDriverInterface $apiDriver
   * @return KpApiClient
   */
  public function setApiDriver(ApiDriverInterface $apiDriver)
  {
    $this->apiDriver = $apiDriver;

    return $this;
  }

  /**
   * Список инстансов
   * @return Error|Instance[]
   */
  public function getInstances()
  {
    if (!$instances = Yii::$app->cache->get(__CLASS__ . __FUNCTION__)) {
      $instances = $this->apiDriver->getInstances();

      Yii::$app->cache->set(__CLASS__ . __FUNCTION__, $instances, 600);
    }

    return $instances;
  }

  /**
   * Список провайдеров
   * @return Error|dto\Provider[]
   */
  public function getProviders()
  {
    if (!$providers = Yii::$app->cache->get(__CLASS__ . __FUNCTION__)) {
      $providers = $this->apiDriver->getProviders();

      Yii::$app->cache->set(__CLASS__ . __FUNCTION__, $providers, 600);
    }

    return $providers;
  }

  /**
   * Создание потока
   * @param CreateStreamRequest $request данные потока
   * @return Error|Stream
   */
  public function createStream(CreateStreamRequest $request)
  {
    return $this->apiDriver->createStream($request);
  }

  /**
   * Список потоков
   * @return Error|Stream[]
   */
  public function getStreams()
  {
    if (!$streams = Yii::$app->cache->get(__CLASS__ . __FUNCTION__)) {
      $streams = $this->apiDriver->getStreams();

      Yii::$app->cache->set(__CLASS__ . __FUNCTION__, $streams, 600);
    }

    return $streams;
  }

  /**
   * @return bool
   */
  public function clearStreamsCache()
  {
    return Yii::$app->cache->delete(__CLASS__ . 'getStreams');
  }

  /**
   * Внешнее тестирование постбека
   * @param $streamId
   * @return bool
   */
  public function testPostback($streamId)
  {
    return $this->apiDriver->testPostback($streamId);
  }


  /**
   * @param AuthRequest $request
   * @return string
   * @inheritdoc
   */
  public function auth(AuthRequest $request)
  {
    return $this->apiDriver->auth($request);
  }

}