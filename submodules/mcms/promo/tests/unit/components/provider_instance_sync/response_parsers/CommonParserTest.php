<?php

namespace mcms\promo\tests\unit\components\provider_instance_sync;

use mcms\common\codeception\TestCase;
use mcms\promo\components\provider_instances_sync\dto\Error;
use mcms\promo\components\provider_instances_sync\response_parsers\StreamResponseParser;

class CommonParserTest extends TestCase
{
  public function testValidResponseError()
  {
    $raw = [
      'success' => false
    ];

    $responseParser = new StreamResponseParser($raw);
    $this->assertTrue($responseParser->isHasError());
    $error = $responseParser->getError();

    $this->assertInstanceOf(Error::class, $error);
  }

  public function testNullResponseError()
  {
    $raw = null;

    $responseParser = new StreamResponseParser($raw);
    $this->assertTrue($responseParser->isHasError());
    $error = $responseParser->getError();

    $this->assertInstanceOf(Error::class, $error);
    $this->assertAttributeEquals('Unknown error', 'name', $error);
  }

  public function testFalseResponseError()
  {
    $raw = false;

    $responseParser = new StreamResponseParser($raw);
    $this->assertTrue($responseParser->isHasError());
    $error = $responseParser->getError();

    $this->assertInstanceOf(Error::class, $error);
    $this->assertAttributeEquals('Unknown error', 'name', $error);
  }

}