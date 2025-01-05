<?php

namespace mcms\promo\components\provider_instances_sync\response_parsers;

use mcms\common\helpers\ArrayHelper;
use mcms\promo\components\provider_instances_sync\dto\Provider;

/**
 * Class ProviderResponseParser
 * @package mcms\mcms\promo\tests\unit\components\provider_instance_sync\response_parsers
 */
class ProvidersResponseParser extends AbstractResponseParser
{
  /**
   * @return Provider[]
   */
  public function parse()
  {
    return array_map(function($rawProviderData) {
      $providerDto = new Provider();
      $providerDto->id = (int) ArrayHelper::getValue($rawProviderData, 'id');
      $providerDto->code =  ArrayHelper::getValue($rawProviderData, 'code');
      $providerDto->name =  ArrayHelper::getValue($rawProviderData, 'name');
      $providerDto->url = ArrayHelper::getValue($rawProviderData, 'url');

      return $providerDto;
    }, $this->getData());
  }

}