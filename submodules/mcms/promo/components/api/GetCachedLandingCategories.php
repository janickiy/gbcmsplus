<?php

namespace mcms\promo\components\api;

use Yii;
use mcms\common\module\api\ApiResult;
use mcms\promo\models\LandingCategory;
use yii\caching\TagDependency;

class GetCachedLandingCategories extends ApiResult
{

  const CACHE_LANDING_CATEGORIES_KEY = 'mcms.partners.promo.landingcategories';
  const DURATION = 1800;

  protected $data;

  protected $cacheTags = ['landing'];

  public function init($params = [])
  {

  }

  public function getResult()
  {
    if (!($this->data = Yii::$app->getCache()->get(self::CACHE_LANDING_CATEGORIES_KEY))) {
      $this->data = LandingCategory::findAll(['status' => LandingCategory::STATUS_ACTIVE]);

      Yii::$app->cache->set(
        self::CACHE_LANDING_CATEGORIES_KEY,
        $this->data,
        self::DURATION,
        new TagDependency(['tags' => $this->cacheTags])
      );
    }

    return $this->data;
  }

  public function invalidateCache()
  {
    Yii::$app->getCache()->delete(self::CACHE_LANDING_CATEGORIES_KEY);
  }
}