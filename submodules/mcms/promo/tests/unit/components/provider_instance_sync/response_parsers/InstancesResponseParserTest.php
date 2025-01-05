<?php

namespace mcms\mcms\promo\tests\unit\components\provider_instance_sync\response_parsers;

use Codeception\TestCase\Test;
use mcms\promo\components\provider_instances_sync\response_parsers\InstancesResponseParser;

class InstancesResponseParserTest extends Test
{
  public function testSuccessDataParse()
  {
    $rawData = [
      'success' => true,
      'data' => [
        [
          'id' => 1,
          'name' => 'test.rgkmobile.local',
          'domain' => 'http://test.rgkmobile.local',
        ],
        [
          'id' => 2,
          'name' => 'test1.rgkmobile.local',
          'domain' => 'http://test1.rgkmobile.local',
        ],
      ],
    ];

    $parser = new InstancesResponseParser($rawData);
    $this->assertFalse($parser->isHasError());

    $actual = $parser->parse();
    $this->assertTrue(count($actual) === 2);

    list($instance1, $instance2) = $actual;

    $this->assertAttributeEquals(1, 'id', $instance1);
    $this->assertAttributeEquals('test.rgkmobile.local', 'name', $instance1);
    $this->assertAttributeEquals('http://test.rgkmobile.local', 'domain', $instance1);

    $this->assertAttributeEquals(2, 'id', $instance2);
    $this->assertAttributeEquals('test1.rgkmobile.local', 'name', $instance2);
    $this->assertAttributeEquals('http://test1.rgkmobile.local', 'domain', $instance2);
  }

  public function testFailedResponseDataParser()
  {
    $rawData = [
      'success' => false,
      'data' => [
        'name' => 'Unauthorized',
        'message' => 'You are requesting with an invalid credential.',
        'code' => '0',
        'status' => '401',
      ],
    ];

    $parser = new InstancesResponseParser($rawData);
    $this->assertTrue($parser->isHasError());
    $error = $parser->getError();

    $this->assertAttributeEquals('Unauthorized', 'name', $error);
    $this->assertAttributeEquals('You are requesting with an invalid credential.', 'message', $error);
    $this->assertAttributeEquals(0, 'code', $error);
    $this->assertAttributeEquals(401, 'status', $error);
  }
}