<?php

namespace mcms\promo\components\provider_instances_sync\response_parsers;

use mcms\common\helpers\ArrayHelper;
use mcms\promo\components\provider_instances_sync\dto\Stream;

/**
 * Class ProviderResponseParser
 * @package mcms\mcms\promo\tests\unit\components\provider_instance_sync\response_parsers
 */
class StreamsResponseParser extends AbstractResponseParser
{
  /**
   * @return Stream[]
   */
  public function parse()
  {
    return array_map(function($rawProviderData) {
      $streamDto = new Stream();
      $streamDto->id = (int) ArrayHelper::getValue($rawProviderData, 'id');
      $streamDto->name = ArrayHelper::getValue($rawProviderData, 'name');
      $streamDto->url = ArrayHelper::getValue($rawProviderData, 'url');
      $streamDto->hash = ArrayHelper::getValue($rawProviderData, 'hash');

      return $streamDto;
    }, $this->getData());
  }

}