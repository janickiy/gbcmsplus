<?php

namespace mcms\payments\components\api;

use mcms\common\module\api\ApiResult;
use mcms\payments\components\events\EarlyPaymentCreated;
use mcms\payments\models\search\UserPaymentSearch;
use mcms\payments\models\UserBalanceInvoice;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseInflector;

class BadgeCounters extends ApiResult
{
  const CACHE_KEY = 'payments_badge_counters_cache';
  private $events = [];

  function init($params = [])
  {

  }

  public function getResult()
  {
    if (!$result = Yii::$app->cache->get($this->cacheKey())) {

      $menuItems = ArrayHelper::getValue(Yii::$app->getModule('payments')->menu, 'items', []);
      foreach ($menuItems as $menuItem) {
        $url = current(ArrayHelper::getValue($menuItem, 'url', []));
        $url = BaseInflector::camelize($url);
        if (!Yii::$app->user->can($url)) continue;
        $events = ArrayHelper::getValue($menuItem, 'events', []);
        if (!count($events)) continue;
        foreach ($events as $event) {
          $this->events[$event] = $url;
        }
      }
      if (array_key_exists(EarlyPaymentCreated::class, $this->events)) {
        $result[EarlyPaymentCreated::class] = $this->awaitingPaymentsCount($this->events[EarlyPaymentCreated::class]);
      }

      $tagDependency = new TagDependency([
        'tags' => [
          $this->cacheKey(),
          self::CACHE_KEY
        ],
      ]);

      Yii::$app->cache->set($this->cacheKey(), $result, 5 * 60, $tagDependency);
    }

    return $result;
  }

  /**
   * @param $url
   * @return int
   */
  private function awaitingPaymentsCount($url)
  {
    // todo заменить на if либо вообще убрать $url из параметра функции
    switch($url) {
      case 'PaymentsPaymentsIndex':
        return (new UserPaymentSearch([
          'status' => [UserPaymentSearch::STATUS_AWAITING, UserPaymentSearch::STATUS_DELAYED],
        ]))->search([])->getTotalCount();
        break;
      default:
        return 0;
    }
  }

  public function invalidateCache()
  {
    TagDependency::invalidate(Yii::$app->cache, self::CACHE_KEY);
  }

  private function cacheKey()
  {
    return 'payments_badge_counters_cache-' . Yii::$app->user->id;
  }

}