<?php

namespace mcms\promo\components\events;

use mcms\promo\Module;
use Yii;

class LinkActivated extends AbstractSource
{
  function getEventName()
  {
    return Yii::_t('promo.events.link_activated');
  }

  public static function getUrl($id = null)
  {
    return ['/partners/links/index/'];
  }

  public function shouldSendNotification()
  {
    /** @var \mcms\promo\Module $promo */
    $promo = Yii::$app->getModule('promo');
    return $promo->isArbitraryLinkModerationActive();
  }

  public function trigger()
  {
    parent::trigger();
    Yii::$app->getModule('notifications')->api('setViewedByIdEvent', [
      'event' => LinkCreatedModeration::class,
      'modelId' => $this->getModelId(),
    ])->getResult();

    Module::getInstance()->api('badgeCounters')->invalidateCache();
  }

  public function getOwner()
  {
    return $this->source->user;
  }
}