<?php

namespace mcms\api\models;

/**
 * модель для апи
 */
class OfferCategory extends \mcms\promo\models\OfferCategory
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
        return $this->hasMany(Landing::class, ['offer_category_id' => 'id']);
    }
}
