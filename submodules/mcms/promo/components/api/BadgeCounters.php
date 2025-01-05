<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\components\events\LandingUnblockRequestCreated;
use mcms\promo\components\events\LinkCreatedModeration;
use mcms\promo\components\events\SourceCreatedModeration;
use mcms\promo\models\search\LandingUnblockRequestSearch;
use mcms\promo\models\search\SourceSearch;
use mcms\promo\Module;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseInflector;

class BadgeCounters extends ApiResult
{
  private $events = [];

  const CACHE_KEY = 'promo_badge_counters_cache';

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
      if (in_array(LandingUnblockRequestCreated::class, $this->events)) {
        $result[LandingUnblockRequestCreated::class] = $this->landingUnblockRequestCount();
      }

      if (in_array(SourceCreatedModeration::class, $this->events)) {
        $result[SourceCreatedModeration::class] = $this->sourceCreatedModerationCount();
      }

      if (in_array(LinkCreatedModeration::class, $this->events)) {
        $result[LinkCreatedModeration::class] = $this->linkCreatedModerationCount();
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

  public function cacheKey()
  {
    return 'promo_badge_counters_cache-' . Yii::$app->user->id;
  }

  public function invalidateCache()
  {
    TagDependency::invalidate(Yii::$app->cache, self::CACHE_KEY);
  }

  private function linkCreatedModerationCount()
  {
    return (new SourceSearch([
      'source_type' => SourceSearch::SOURCE_TYPE_LINK,
      'status' => SourceSearch::STATUS_MODERATION
    ]))->search([])->getTotalCount();
  }

  private function sourceCreatedModerationCount()
  {
    return (new SourceSearch([
      'source_type' => SourceSearch::SOURCE_TYPE_WEBMASTER_SITE,
      'status' => SourceSearch::STATUS_MODERATION
    ]))->search([])->getTotalCount();
  }

  private function landingUnblockRequestCount()
  {
    return (new LandingUnblockRequestSearch)->search([])->getTotalCount();
  }
}