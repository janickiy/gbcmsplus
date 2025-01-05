<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use mcms\promo\models\Source;
use Yii;

/**
 * Class SourceStatusChanged
 * @package mcms\promo\components\events
 */
class SourceRejected extends AbstractSource
{
  public function getOwner()
  {
    return $this->source->user;
  }

  public static function getUrl($id = null)
  {
    return ['/partners/sources/index/'];
  }


  /**
   * @return string
   */
  function getEventName()
  {
    return Yii::_t('promo.events.source_status_inactive');
  }
}