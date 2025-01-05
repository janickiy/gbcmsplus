<?php


namespace mcms\promo\components\api;

use mcms\promo\models\Banner;
use mcms\promo\models\LandingCategory;
use mcms\promo\models\Source as SourceModel;
use mcms\promo\Module;
use Yii;
use mcms\common\module\api\ApiResult;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * Class Banner
 * @package mcms\promo\components\api
 */
class Banners extends ApiResult
{
  const CACHE_PREFIX = 'selected_banner_';
  const GLOBAL_CACHE_TAG = self::CACHE_PREFIX . '_global';

  /** @var  int */
  private $sourceId;
  /** @var  SourceModel */
  private $source;
  /** @var  LandingCategory */
  private $category;

  /** @var  string */
  private $cacheKey;

  /**
   * @inheritdoc
   */
  public function init($params = [])
  {
    $this->sourceId = ArrayHelper::getValue($params, 'sourceId');
    $this->source = $this->sourceId ? SourceModel::findOne($this->sourceId) : null;
    $this->category = $this->sourceId ? LandingCategory::findOne($this->source->category_id) : null;
    $this->cacheKey = self::CACHE_PREFIX . '_source_id_' . $this->sourceId;
  }

  /**
   * @return string
   */
  public function getSelected()
  {
    $cachedData = Yii::$app->cache->get($this->cacheKey);

    if ($cachedData) return $cachedData;

    if ($banners = $this->getBySource()) return $this->saveToCache($banners);
    if ($banners = $this->getByCategory()) return $this->saveToCache($banners);
    if ($banner = $this->getModuleDefaultBanner()) return $this->saveToCache([$banner]);

    return $this->saveToCache(null);
  }

  /**
   * @param Banner[] $banners
   * @return Banner[]|null
   */
  private function saveToCache(array $banners = null)
  {
    $cacheDependency = new TagDependency(['tags' => [
      self::GLOBAL_CACHE_TAG
    ]]);

    Yii::$app->cache->set($this->cacheKey, $banners, 3600, $cacheDependency);

    return $banners;
  }

  public static function clearSelectedBannerCache()
  {
    TagDependency::invalidate(Yii::$app->cache, [
      self::GLOBAL_CACHE_TAG
    ]);
  }

  /**
   * @return Banner[]|null
   */
  private function getBySource()
  {
    if (!$this->source) return null;

    return $this->source->getBanners(true)->all();
  }

  /**
   * @return Banner[]|null
   */
  private function getByCategory()
  {
    if (!$this->category) return null;

    return $this->category->getBanners(true)->all();
  }

  /**
   * @return Banner|null
   */
  private function getModuleDefaultBanner()
  {
    $banner = Banner::findOne(['is_default' => 1]);

    if (!$banner) return null;
    if ($banner->is_disabled) return null;

    return $banner;
  }
}


