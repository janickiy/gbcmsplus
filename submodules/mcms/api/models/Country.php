<?php

namespace mcms\api\models;

/**
 * модель для апи
 */
class Country extends \mcms\promo\models\Country
{
  public $totalRevenue;
  public $cpaRevenue;
    public $revshareRevenue;
  public $otpRevenue;

  /**
   * Это чтоб модель бралась из апи, а не общая
   * @return \yii\db\ActiveQuery
   */
  public function getOperators()
  {
    return $this->hasMany(Operator::class, ['country_id' => 'id']);
  }
}
