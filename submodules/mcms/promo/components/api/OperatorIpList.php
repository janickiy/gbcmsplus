<?php


namespace mcms\promo\components\api;


use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\promo\models\search\OperatorIpSearch;
use Yii;
use yii\caching\TagDependency;

class OperatorIpList extends ApiResult
{

  private $cacheTags;
  private $cacheKey;
  private $params;

  const CACHE_KEY_PREFIX = 'operator_ips__';

  public function init($params = [])
  {
    $this->cacheKey = self::CACHE_KEY_PREFIX . serialize($params);
    $this->prepareDataProvider(new OperatorIpSearch(), $params);
    $this->params = $params;
  }

  public function getResult()
  {

    if (!$this->getDataProvider()) {
      $this->addError('dataProvider is not prepared');
      return false;
    }

    // If cache value exist
    if ($cachedResult = Yii::$app->cache->get($this->cacheKey)) return $cachedResult;

    // If cache NOT exist, fetching result
    $result = ArrayHelper::toArray($this->getDataProvider()->getModels(), [], true);

    // Put result to cache
    $this->cacheTags = [
      self::CACHE_KEY_PREFIX . 'operatorid' . $this->getOperatorId()
    ];

    $cacheDependency = new TagDependency(['tags' => $this->cacheTags]);
    Yii::$app->cache->set($this->cacheKey, $result, 0, $cacheDependency);

    return $result;
  }

  private function getOperatorId()
  {
    $operatorId = '';
    if ($conditions = ArrayHelper::getValue($this->params, 'conditions')){
      $operatorId = ArrayHelper::getValue($conditions, 'operator_id', '');
    }
    return $operatorId;
  }
}