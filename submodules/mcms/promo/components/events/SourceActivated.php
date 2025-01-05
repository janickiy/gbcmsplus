<?php
namespace mcms\promo\components\events;

use mcms\promo\Module;
use Yii;

/**
 * Class SourceStatusChanged
 * @package mcms\promo\components\events
 */
class SourceActivated extends AbstractSource
{
  public function getOwner()
  {
    return $this->source->user;
  }

  public function getModelId()
  {
    return $this->source->id;
  }

  public static function getUrl($id = null)
  {
    return ['/partners/sources/index/'];
  }

  public function trigger()
  {
    parent::trigger();
    Yii::$app->getModule('notifications')->api('setViewedByIdEvent', [
      'event' => SourceCreatedModeration::class,
      'modelId' => $this->getModelId(),
    ])->getResult();

    Module::getInstance()->api('badgeCounters')->invalidateCache();
  }


  /**
   * @return string
   */
  function getEventName()
  {
    return Yii::_t('promo.events.source_status_activated');
  }
}