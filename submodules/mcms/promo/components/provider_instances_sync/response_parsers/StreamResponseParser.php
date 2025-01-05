<?php

namespace mcms\promo\components\provider_instances_sync\response_parsers;

use mcms\promo\components\provider_instances_sync\dto\Stream;
use yii\helpers\ArrayHelper;

/**
 * Class StreamResponseParser
 * @package mcms\promo\components\provider_instances_sync\response_parsers
 */
class StreamResponseParser extends AbstractResponseParser
{
  /**
   * @return Stream
   */
  public function parse()
  {
    $data = $this->getData();

    $streamDto = new Stream();
    $streamDto->id = (int) ArrayHelper::getValue($data, 'id');
    $streamDto->name = ArrayHelper::getValue($data, 'name');
    $streamDto->url = ArrayHelper::getValue($data, 'url');
    $streamDto->hash = ArrayHelper::getValue($data, 'hash');

    return $streamDto;
  }

}