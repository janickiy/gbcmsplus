<?php

namespace mcms\promo\tests\unit\components\provider_instance_sync\response_parsers;

use Codeception\TestCase\Test;
use mcms\promo\components\provider_instances_sync\response_parsers\ProvidersResponseParser;


class ProvidersResponseParserTest extends Test
{
  public function testSuccessDataParse()
  {
    $rawData = [
      'success' => true,
      'data' => [
        [
          'id' => 1,
          'name' => 'first',
          'code' => 'in',
          'url' => 'https://in.rgkmobile.com',
        ],
        [
          'id' => 2,
          'code' => 'in2',
          'name' => 'second',
          'url' => 'https://1in.rgkmobile.com',
        ],
      ],
    ];

    $parser = new ProvidersResponseParser($rawData);
    $this->assertFalse($parser->isHasError());

    $actual = $parser->parse();
    $this->assertTrue(count($actual) === 2);

    list($provider1, $provider2) = $actual;

    $this->assertAttributeEquals(1, 'id', $provider1);
    $this->assertAttributeEquals('in', 'code', $provider1);
    $this->assertAttributeEquals('https://in.rgkmobile.com', 'url', $provider1);
    $this->assertAttributeEquals('first', 'name', $provider1);

    $this->assertAttributeEquals(2, 'id', $provider2);
    $this->assertAttributeEquals('in2', 'code', $provider2);
    $this->assertAttributeEquals('https://1in.rgkmobile.com', 'url', $provider2);
    $this->assertAttributeEquals('second', 'name', $provider2);
  }
}