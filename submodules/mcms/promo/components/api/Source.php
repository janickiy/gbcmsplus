<?php


namespace mcms\promo\components\api;


use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\common\module\api\join\Query;
use mcms\promo\models\Stream;
use Yii;

class Source extends ApiResult
{

  private $hash;
  protected $cacheKey;

  const CACHE_KEY_PREFIX = 'source__';

  public function init($params = [])
  {
    $this->hash = ArrayHelper::getValue($params, 'hash', null);
    if (!$this->hash) $this->addError('hash is not set');

    $this->cacheKey = self::CACHE_KEY_PREFIX . $this->hash;
  }

  public function getResult()
  {
    if (!$this->hash) return false;

    // If cache value exist
    if ($cachedResult = Yii::$app->cache->get($this->cacheKey)) return $cachedResult;

    // If cache NOT exist, fetching result:
    $source = \mcms\promo\models\Source::find()
      ->where(['hash' => $this->hash])
      ->with(['domain', 'stream', 'sourceOperatorLanding'])
      ->one();

    if (!$source) {
      $this->addError('source not found by provided hash');
      return false;
    }

    $sourceArr = ArrayHelper::toArray($source);

    foreach($source->getRelatedRecords() as $relationName => $relation){
      $sourceArr[$relationName] = ArrayHelper::toArray($relation);
    }

    // Put result to cache
    Yii::$app->cache->set($this->cacheKey, $sourceArr);

    return $sourceArr;
  }

  public function join(Query &$query, $column = 'id')
  {
    $query
      ->setRightTable('sources')
      ->setRightTableColumn($column)
      ->join()
    ;
  }
}