<?php

namespace mcms\promo\components\provider_instances_sync\response_parsers;

use mcms\common\helpers\ArrayHelper;
use mcms\promo\components\provider_instances_sync\dto\Instance;

class InstancesResponseParser extends AbstractResponseParser
{
  /**
   * @return Instance[]
   */
  public function parse()
  {
    return array_map(function($instanceRawData){
      $instanceDto = new Instance();
      $instanceDto->name = ArrayHelper::getValue($instanceRawData, 'name');
      $instanceDto->id = (int) ArrayHelper::getValue($instanceRawData, 'id');
      $instanceDto->domain = ArrayHelper::getValue($instanceRawData, 'sync_domain');

      return $instanceDto;
    }, $this->getData());
  }
}