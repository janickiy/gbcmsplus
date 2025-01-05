<?php

namespace mcms\api\models;

/**
 * модель для апи
 */
class LandingCategory extends \mcms\promo\models\LandingCategory
{
  public $totalRevenue;
  public $cpaRevenue;
  public $revshareRevenue;
  public $otpRevenue;

  /**
   * Это чтоб модель бралась из апи, а не общая
   * @return \yii\db\ActiveQuery
   */
  public function getLandings()
  {
    return $this->hasMany(Landing::class, ['category_id' => 'id']);
  }
}
