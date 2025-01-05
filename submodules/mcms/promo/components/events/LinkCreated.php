<?php
namespace mcms\promo\components\events;

use Yii;

class LinkCreated extends AbstractSource
{
  function getEventName()
  {
    return Yii::_t('promo.events.link_created');
  }

  public function trigger()
  {
    if ($this->source->isStatusModeration()) {
      (new LinkCreatedModeration($this->source))->trigger();
      return ;
    }
    parent::trigger();
  }

  public function shouldSendNotification()
  {
    return Yii::$app->authManager->checkAccess($this->source->user_id, 'CanLinkNotificationSend');
  }

  public static function getUrl($id = null)
  {
    return ['/promo/arbitrary-sources/index/'];
  }

}