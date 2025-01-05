<?php

namespace mcms\promo\components\events;

use mcms\common\event\Event;
use mcms\promo\models\Source;

abstract class AbstractSource extends Event
{
  public $source;

  public function __construct(Source $source = null)
  {
    $this->source = $source;
  }

  public static function getUrl($id = null)
  {
    return ['/promo/webmaster-sources/index/'];
  }

  public function getModelId()
  {
    return $this->source ? $this->source->id : null;
  }

}