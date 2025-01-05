<?php
namespace mcms\promo\models\traits;
use mcms\promo\models\Banner;

/**
 * Общие методы для моделей Source и LandingCategory
 * Class LinkBanners
 * @package mcms\promo\models\traits
 */
trait LinkBanners
{
  /**
   * @var array ID баннеров привязанных к источнику
   */
  public $bannersIds = [];

  /**
   * Прикреление нового списка баннеров
   */
  public function linkBanners()
  {
    $this->unlinkAll('banners', true);
    if (empty($this->bannersIds)) {
      return;
    }
    /** @var Banner $banner */
    foreach (Banner::find()->where(['id' => $this->bannersIds])->all() as $banner) {
      $this->link('banners', $banner);
    }
  }

  /**
   * Получение ID баннеров привязанных к источнику
   */
  public function updateBannersIds()
  {
    $this->bannersIds = $this->getBanners()->select('id')->column();
  }

  /**
   * @param bool|null $isActive
   * @return \yii\db\ActiveQuery
   */
  public function getBanners($isActive = null)
  {
    $query = $this->hasMany(Banner::class, ['id' => 'banner_id'])
      ->viaTable(static::LINK_BANNERS_TABLE, [static::LINK_BANNERS_FIELD => 'id']);
    if ($isActive !== null) {
      $query->andWhere(['is_disabled' => $isActive ? 0 : 1]);
    }
    return $query;
  }
}