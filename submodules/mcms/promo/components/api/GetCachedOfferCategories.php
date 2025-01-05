<?php

namespace mcms\promo\components\api;

use mcms\promo\models\Country;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingOperator;
use mcms\promo\models\LandingUnblockRequest;
use mcms\promo\models\OfferCategory;
use mcms\promo\models\Operator;
use Yii;
use mcms\common\module\api\ApiResult;
use yii\caching\TagDependency;
use yii\db\Query;

class GetCachedOfferCategories extends ApiResult
{

  const CACHE_OFFER_CATEGORIES_KEY = 'mcms.partners.promo.offercategories';
  const DURATION = 1800;

  protected $data;

  protected $cacheTags = ['landing', self::CACHE_OFFER_CATEGORIES_KEY];

  public function init($params = [])
  {

  }

  public function getResult()
  {
    if (!($this->data = Yii::$app->getCache()->get($this->getCacheKey()))) {
      $this->data = [
        'country' => [],
        'operator' => [],
        'categories' => [],
      ];

      $ctTable = Country::tableName();
      $lTable = Landing::tableName();
      $lUrTable = LandingUnblockRequest::tableName();
      $loTable = LandingOperator::tableName();
      $opTable = Operator::tableName();
      $ocTable = OfferCategory::tableName();
      $userId= Yii::$app->user->id;

      $query = (new Query())
        ->select([
          $opTable . '.id AS operator_id',
          $opTable . '.country_id AS country_id',
          $ocTable . '.id AS offer_category_id'
        ])
        ->from([
          $loTable,
        ])
        ->innerJoin($lTable, sprintf('%s.`id` = %s.`landing_id`', $lTable, $loTable))
        ->leftJoin($lUrTable, sprintf('%s.`id` = %s.`landing_id` AND %s.`user_id` = %s', $lTable, $lUrTable, $lUrTable, $userId))
        ->innerJoin($opTable, sprintf('%s.`id` = %s.`operator_id`', $opTable, $loTable))
        ->innerJoin($ctTable, sprintf('%s.`id` = %s.`country_id`', $ctTable, $opTable))
        ->innerJoin($ocTable, sprintf('%s.id = %s.offer_category_id', $ocTable, $lTable))
        ->andWhere(['=', $lTable . '.status', Landing::STATUS_ACTIVE])
        ->andWhere(['=', $opTable . '.status', Operator::STATUS_ACTIVE])
        ->andWhere(['=', $ocTable . '.status', OfferCategory::STATUS_ACTIVE])
        ->andWhere(['=', $ctTable . '.status', Country::STATUS_ACTIVE])
        ->andWhere(['or',
          ['!=', $lTable . '.access_type', Landing::ACCESS_TYPE_HIDDEN],
          [$lUrTable . '.status' => LandingUnblockRequest::STATUS_UNLOCKED],
        ])
        ->groupBy(['operator_id', 'offer_category_id']);

      foreach ($query->each() as $row) {
        $this->data['country'][$row['country_id']][] = $row['offer_category_id'];
        $this->data['operator'][$row['operator_id']][] = $row['offer_category_id'];
        $this->data['categories'][] = $row['offer_category_id'];
      }

      foreach ($this->data['country'] as $countryId => $payTypes) {
        $this->data['country'][$countryId] = array_unique($payTypes);
      }

      foreach ($this->data['operator'] as $operatorId => $payTypes) {
        $this->data['operator'][$operatorId] = array_unique($payTypes);
      }

      $this->data['categories'] = array_unique($this->data['categories']);


      Yii::$app->cache->set(
        $this->getCacheKey(),
        $this->data,
        self::DURATION,
        new TagDependency(['tags' => $this->cacheTags])
      );
    }

    return $this;
  }

  public function getCountryOfferCategories()
  {
    return $this->data['country'];
  }

  public function getOperatorOfferCategories()
  {
    return $this->data['operator'];
  }

  public function getOfferCategories()
  {
    return $this->data['categories'];
  }

  public function invalidateCache()
  {
    $this->invalidateAll();
  }

  /**
   *
   */
  public function invalidateAll()
  {
    TagDependency::invalidate(Yii::$app->cache, self::CACHE_OFFER_CATEGORIES_KEY);
  }

  /**
   * @param int $userId
   * @return string
   */
  protected function getCacheKey($userId = null)
  {
    $userId || $userId = Yii::$app->user->id;

    return sprintf('%s.%s', self::CACHE_OFFER_CATEGORIES_KEY, $userId);
  }
}