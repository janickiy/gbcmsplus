<?php

namespace mcms\promo\components\provider_instances_sync\api_drivers;

use mcms\promo\components\provider_instances_sync\dto\Error;
use mcms\promo\components\provider_instances_sync\dto\Instance;
use mcms\promo\components\provider_instances_sync\dto\Provider;
use mcms\promo\components\provider_instances_sync\dto\Stream;
use mcms\promo\components\provider_instances_sync\requests\AuthRequest;
use mcms\promo\components\provider_instances_sync\requests\CreateStreamRequest;

class FakeApiDriver implements ApiDriverInterface
{
  public function __construct($instance = null)
  {

  }

  public function auth(AuthRequest $request)
  {
    return 'test-api-token';
  }

  /**
   * @return Stream[]
   */
  public function getStreams()
  {
    $streams = [];

    $streams[] = (new Stream())
      ->setId(1)
      ->setHash(uniqid())
      ->setName('First Stream')
      ->setUrl('http://test-tds-url.local/')
    ;

    $streams[] = (new Stream())
      ->setId(2)
      ->setHash(uniqid())
      ->setName('Second Stream')
      ->setUrl('http://test-tds-url.local/')
      ;

    $streams[] = (new Stream())
      ->setId(3)
      ->setHash(uniqid())
      ->setName('Third')
      ->setUrl('http://test-tds-url.local/')
    ;

    return $streams;
  }

  /**
   * @param CreateStreamRequest $request
   * @return Stream
   */
  public function createStream(CreateStreamRequest $request)
  {
    $error = new Error();
    $error->message = 'Unknown error';
    $error->code = 500;

    return $error;

    return (new Stream())
      ->setId(4)
      ->setName($request->name)
      ->setHash(uniqid())
      ->setUrl('http://test-tds-url.local/')
    ;
  }

  /**
   * @return Provider[]
   */
  public function getProviders()
  {
    $providers = [];
    $providers[] = (new Provider())
      ->setId(1)
      ->setHash('xxx')
      ->setCode('kz')
      ->setEmail('example@rgkmobile.com')
      ->setUrl('http://kz.test.local')
      ;

    $providers[] = (new Provider())
      ->setId(2)
      ->setHash('yyy')
      ->setCode('by')
      ->setEmail('example@rgkmobile.com')
      ->setUrl('http://by.test.local')
    ;

    return $providers;
  }

  /**
   * @return Instance[]
   */
  public function getInstances()
  {
    $instances = [];
    $instances[] = (new Instance())
      ->setId(1)
      ->setName('kz')
      ->setDomain('http://kz.test.local')
      ;

    $instances[] = (new Instance())
      ->setId(2)
      ->setName('by')
      ->setDomain('http://by.test.local')
      ;

    return $instances;
  }

  /**
   * @inheritdoc
   */
  public function testPostback($streamId)
  {
    return true;
  }


}