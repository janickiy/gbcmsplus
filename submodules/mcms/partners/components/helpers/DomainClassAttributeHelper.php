<?php

namespace mcms\partners\components\helpers;

use yii\base\Object;

class DomainClassAttributeHelper extends Object
{

  const DOMAIN_CLASS_ACTIVE = 'normal';
  const DOMAIN_CLASS_BANNED = 'virus';

  public static function getDomainClass($isActive)
  {
    return $isActive ? self::DOMAIN_CLASS_ACTIVE : self::DOMAIN_CLASS_BANNED;
  }

}
