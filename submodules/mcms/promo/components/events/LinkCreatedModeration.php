<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use mcms\promo\Module;
use Yii;
use mcms\promo\models\Source;
use yii\helpers\Url;

class LinkCreatedModeration extends AbstractSource
{
  function getEventName()
  {
    return Yii::_t('promo.events.link_created_moderation');
  }

  public function incrementBadgeCounter()
  {
    return $this->source->isStatusModeration();
  }

  public function shouldSendNotification()
  {
    return Yii::$app->authManager->checkAccess($this->source->user_id, 'CanLinkNotificationSend');
  }

  public function getModelId()
  {
    return $this->source->id;
  }

  public static function getUrl($id = null)
  {
    return ['/promo/arbitrary-sources/update/', 'id' => $id];
  }

  public function trigger()
  {
    parent::trigger();
    Module::getInstance()->api('badgeCounters')->invalidateCache();
  }


}