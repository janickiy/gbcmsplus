<?php

namespace mcms\promo\tests\unit\components\provider_instance_sync\response_parsers;

use Codeception\TestCase\Test;
use mcms\promo\components\provider_instances_sync\response_parsers\StreamResponseParser;

class StreamResponseParserTest extends Test
{
  public function testSuccessDataParse()
  {
    $rawData = [
      'success' => true,
      'data' => [
        'id' => '1',
        'name' => 'aaa',
        'hash' => 'sdsdsdsd',
        'url' => 'http://url.com',
      ],
    ];

    $parser = new StreamResponseParser($rawData);
    $this->assertFalse($parser->isHasError());

    $actual = $parser->parse();

    $this->assertAttributeEquals(1, 'id', $actual);
    $this->assertAttributeEquals('aaa', 'name', $actual);
    $this->assertAttributeEquals('sdsdsdsd', 'hash', $actual);
    $this->assertAttributeEquals('http://url.com', 'url', $actual);
  }
}