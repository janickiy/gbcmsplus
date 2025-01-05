<?php

namespace mcms\partners\components\api;

use mcms\common\module\api\ApiResult;
use mcms\support\components\events\EventCreated;
use mcms\support\components\events\EventMessageSend;
use mcms\support\models\search\SupportSearch;
use mcms\support\Module;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseInflector;

class CountOfTickets extends ApiResult
{
  const CACHE_KEY = 'support_badge_counters_cache';

  private $url = '/partners/support/index';

  function init($params = [])
  {

  }

  public function getResult($useCache = true)
  {
    if (!Yii::$app->user->can(BaseInflector::camelize($this->url))) {
      return false;
    }

    if ($useCache && $result = Yii::$app->cache->get($this->cacheKey())) {
      return $result;
    }

    $result = $this->unreadMessagesCount();

    $tagDependency = new TagDependency([
      'tags' => [
        $this->cacheKey(),
        self::CACHE_KEY,
      ]
    ]);

    Yii::$app->cache->set($this->cacheKey(), $result, 5 * 60, $tagDependency);

    return $result;
  }

  private function cacheKey()
  {
    return 'support_badge_counters_cache-' . Yii::$app->user->id;
  }

  private function unreadMessagesCount()
  {
    return (new SupportSearch([
      'owner_has_unread_messages' => true,
      'created_by' => Yii::$app->user->id,
      'is_opened' => 1,
    ]))->search([])->getTotalCount();
  }

}