<?php

namespace mcms\user\components\api;

use mcms\common\module\api\ApiResult;
use mcms\user\components\events\EventRegisteredHandActivation;
use mcms\user\models\User as UserModel;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseInflector;

class BadgeCounters extends ApiResult
{

    const CACHE_KEY = 'users_badge_counters_cache';
    private $events = [];

    function init($params = [])
    {

    }

    public function getResult()
    {

        if (!$result = Yii::$app->cache->get(self::CACHE_KEY)) {
            $menuItems = ArrayHelper::getValue(Yii::$app->getModule('users')->menu, 'items', []);
            foreach ($menuItems as $menuItem) {
                $url = current(ArrayHelper::getValue($menuItem, 'url', []));
                if (!Yii::$app->user->can(BaseInflector::camelize($url))) continue;
                $events = ArrayHelper::getValue($menuItem, 'events', []);
                $this->events = array_merge($this->events, $events);
            }

            if (in_array(EventRegisteredHandActivation::class, $this->events)) {
                $result[EventRegisteredHandActivation::class] = $this->handActivationCount();
            }

            $tagDependency = new TagDependency([
                'tags' => [
                    $this->cacheKey(),
                    self::CACHE_KEY
                ]
            ]);

            Yii::$app->cache->set(self::CACHE_KEY, $result, 60 * 5, $tagDependency);
        }

        return $result;

    }

    private function cacheKey()
    {
        return 'users_badge_counters_cache-' . Yii::$app->user->id;
    }

    public function invalidateCache()
    {
        TagDependency::invalidate(Yii::$app->cache, self::CACHE_KEY);
    }

    private function handActivationCount()
    {
        return (new \mcms\user\models\search\User([
            'status' => UserModel::STATUS_ACTIVATION_WAIT_HAND,
        ]))->search([])->getTotalCount();
    }
}
