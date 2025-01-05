<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\models\search\OperatorSearch;
use Yii;
use yii\caching\TagDependency;

class TrialOperators extends ApiResult
{
  private $cacheTags;
  private $cacheKey;

  const CACHE_KEY_PREFIX = 'trialOperators';

  /**
   * @param array $params
   */
  public function init($params = [])
  {
    $this->cacheKey = self::CACHE_KEY_PREFIX;
  }

  /**
   * @return array|mixed
   */
  public function getResult()
  {
    if ($cachedResult = Yii::$app->cache->get($this->cacheKey)) return $cachedResult;

    $result = OperatorSearch::find()->select('id')->where(['is_trial' => true])->column();

    $this->cacheTags = [
      'operator'
    ];

    $cacheDependency = new TagDependency(['tags' => $this->cacheTags]);
    Yii::$app->cache->set($this->cacheKey, $result, 0, $cacheDependency);

    return $result;
  }


}