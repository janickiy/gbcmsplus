<?php

namespace mcms\statistic\components;

use mcms\statistic\models\ResellerHoldRule;
use yii\base\Object;
use yii\helpers\Json;

/**
 * Class ResellerUnholdRuleHash
 * @package mcms\statistic\components
 */
class ResellerUnholdRuleHash extends Object
{
  /** @var  ResellerHoldRule */
  public $holdRule;

  /**
   * первые 8 символов хэша мд5 настроек для json-сериализованной модели
   * @return string
   */
  public function getHash()
  {
    return substr(md5(
      Json::encode(
        $this->holdRule->toArray([
          'unholdRange',
          'unholdRangeType',
          'minHoldRange',
          'minHoldRangeType',
          'atDay',
          'atDayType',
        ])
      )), 0, 8);
  }
}