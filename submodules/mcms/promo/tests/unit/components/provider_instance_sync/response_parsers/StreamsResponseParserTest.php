<?php

namespace mcms\promo\tests\unit\components\provider_instance_sync\response_parsers;

use Codeception\TestCase\Test;
use mcms\promo\components\provider_instances_sync\response_parsers\ProvidersResponseParser;
use mcms\promo\components\provider_instances_sync\response_parsers\StreamsResponseParser;


class StreamsResponseParserTest extends Test
{
  public function testSuccessDataParse()
  {
    $rawData = [
      'success' => true,
      'data' => [
        [
          'id' => '1',
          'name' => 'aaa',
          'hash' => 'sdsdsdsd',
          'url' => 'http://url.com',
        ],
        [
          'id' => '2',
          'name' => 'bbb',
          'hash' => 'bbbasass',
          'url' => 'http://url1.com',
        ],
      ],
    ];

    $parser = new StreamsResponseParser($rawData);
    $this->assertFalse($parser->isHasError());

    $actual = $parser->parse();
    $this->assertTrue(count($actual) === 2);

    list($stream, $stream2) = $actual;

    $this->assertAttributeEquals(1, 'id', $stream);
    $this->assertAttributeEquals('aaa', 'name', $stream);
    $this->assertAttributeEquals('sdsdsdsd', 'hash', $stream);
    $this->assertAttributeEquals('http://url.com', 'url', $stream);

    $this->assertAttributeEquals(2, 'id', $stream2);
    $this->assertAttributeEquals('bbb', 'name', $stream2);
    $this->assertAttributeEquals('bbbasass', 'hash', $stream2);
    $this->assertAttributeEquals('http://url1.com', 'url', $stream2);
  }
}