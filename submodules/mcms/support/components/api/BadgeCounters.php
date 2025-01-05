<?php

namespace mcms\support\components\api;

use mcms\common\module\api\ApiResult;
use mcms\support\components\events\EventMessageSend;
use mcms\support\models\search\SupportSearch;
use mcms\support\Module;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseInflector;

class BadgeCounters extends ApiResult
{
  private $events = [];

  const CACHE_KEY = 'support_badge_counters_cache';

  function init($params = [])
  {

  }

  public function getResult()
  {
    if (!$result = Yii::$app->cache->get($this->cacheKey())) {
      $menuItems = ArrayHelper::getValue(Module::getInstance()->menu, 'items', []);
      foreach ($menuItems as $menuItem) {
        $url = current(ArrayHelper::getValue($menuItem, 'url', []));
        if (!Yii::$app->user->can(BaseInflector::camelize($url))) continue;
        $events = ArrayHelper::getValue($menuItem, 'events', []);
        $this->events = array_merge($this->events, $events);
      }

      if (in_array(EventMessageSend::class, $this->events)) {
        if(!is_array($result)) $result = [];
        $result[EventMessageSend::class] = $this->unreadMessagesCount();
      }

      $tagDependency = new TagDependency([
        'tags' => [
          $this->cacheKey(),
          self::CACHE_KEY,
        ]
      ]);

      Yii::$app->cache->set($this->cacheKey(), $result, 5 * 60, $tagDependency);
    }
    return $result;
  }

  public function invalidateCache()
  {
    TagDependency::invalidate(Yii::$app->cache, self::CACHE_KEY);
  }

  private function cacheKey()
  {
    return 'support_badge_counters_cache-' . Yii::$app->user->id;
  }

  private function unreadMessagesCount()
  {
    return (new SupportSearch([
      'has_unread_messages' => true,
      'is_opened' => 1,
    ]))->search([])->getTotalCount();
  }

}