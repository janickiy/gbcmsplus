<?php

namespace mcms\promo\components\api;

use mcms\common\exceptions\ParamRequired;
use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\promo\Module;
use Yii;
use yii\caching\TagDependency;
use mcms\promo\models\RebillCorrectConditions as Model;

/**
 * Class RebillCorrectConditions
 * @package mcms\promo\components\api
 */
class RebillCorrectConditions extends ApiResult
{

  /**
   * @var
   */
  protected $partnerId;
  /**
   * @var
   */
  protected $operatorId;
  /**
   * @var
   */
  protected $landingId;

  /**
   * @var Module
   */
  protected $module;


  const CACHE_KEY_PREFIX = 'correct_rebill_conditions__';

  /**
   * @param array $params
   */
  public function init($params = [])
  {
    $this->operatorId = ArrayHelper::getValue($params, 'operatorId');
    $this->landingId = ArrayHelper::getValue($params, 'landingId');
    $this->partnerId = ArrayHelper::getValue($params, 'partnerId');

    $this->module = Yii::$app->getModule('promo');
  }

  /**
   * По параметрам [operatorId] [landingId] [partnerId]
   * ищем соответствующий процент корректировки.
   *
   * @return null|Model
   */
  public function getPercent()
  {
    $cacheKey = self::CACHE_KEY_PREFIX . sprintf(
        'landing%s-operator%s-partner%s',
        $this->landingId,
        $this->operatorId,
        $this->partnerId
      );

    $cachedResult = Yii::$app->cache->get($cacheKey);
    if ($cachedResult) return $cachedResult;

    $cacheTags = [
      self::CACHE_KEY_PREFIX . 'partner_id' . $this->partnerId,
      self::CACHE_KEY_PREFIX . 'operator_id' . $this->operatorId,
      self::CACHE_KEY_PREFIX . 'landing_id' . $this->landingId,
      self::CACHE_KEY_PREFIX . 'global'
    ];

    $result = $this->getPercentByAttributes();

    // Put result to cache
    $cacheDependency = new TagDependency(['tags' => $cacheTags]);
    Yii::$app->cache->set($cacheKey, $result, 3600, $cacheDependency);

    return $result;
  }

  /**
   * @return null|Model
   */
  protected function getPercentByAttributes()
  {
    // лендингу, оператору, партнеру
    if ($this->operatorId && $this->landingId && $this->partnerId) {
      $fetch = $this->getOneByAttributes($this->operatorId, $this->landingId, $this->partnerId);
      if ($fetch) return $fetch;
    }

    // по лендингу, партнеру
    if ($this->landingId && $this->partnerId) {
      $fetch = $this->getOneByAttributes(null, $this->landingId, $this->partnerId);
      if ($fetch) return $fetch;
    }

    // по оператору, партнеру
    if ($this->operatorId && $this->partnerId) {
      $fetch = $this->getOneByAttributes($this->operatorId, null, $this->partnerId);
      if ($fetch) return $fetch;
    }

    // по партнеру
    if ($this->partnerId) {
      $fetch = $this->getOneByAttributes(null, null, $this->partnerId);
      if ($fetch) return $fetch;
    }
    // лендингу, оператору
    if ($this->operatorId && $this->landingId) {
      $fetch = $this->getOneByAttributes($this->operatorId, $this->landingId, null);
      if ($fetch) return $fetch;
    }

    // по лендингу
    if ($this->landingId) {
      $fetch = $this->getOneByAttributes(null, $this->landingId, null);
      if ($fetch) return $fetch;
    }

    // по оператору
    if ($this->operatorId) {
      $fetch = $this->getOneByAttributes($this->operatorId, null, null);
      if ($fetch) return $fetch;
    }

    // глобальный (без указания оператора, лендинг, партнера)
    $fetch = $this->getOneByAttributes(null, null, null);
    if ($fetch) return $fetch;

    // Не найдено ни одно условие в БД
    return null;
  }

  /**
   * @param null $operatorId
   * @param null $landingId
   * @param null $partnerId
   * @return null|Model
   */
  protected function getOneByAttributes($operatorId = null, $landingId = null, $partnerId = null)
  {
    $query = Model::find();

    $query->andWhere($operatorId === null ? 'operator_id IS NULL' : ['operator_id' => $operatorId]);
    $query->andWhere($landingId === null ? 'landing_id IS NULL' : ['landing_id' => $landingId]);
    $query->andWhere($partnerId === null ? 'partner_id IS NULL' : ['partner_id' => $partnerId]);

    return $query->one();
  }
}


