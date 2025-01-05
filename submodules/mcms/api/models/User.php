<?php

namespace mcms\api\models;

use mcms\user\models\UserStaticAR;

/**
 * модель для апи
 */
class User extends UserStaticAR
{
    public $totalRevenue;
    public $cpaRevenue;
    public $revshareRevenue;
    public $otpRevenue;

    /**
     * Это чтоб модель бралась из апи, а не общая
     * @return \yii\db\ActiveQuery
     */
    public function getSources()
    {
        return $this->hasMany(Source::class, ['user_id' => 'id']);
    }
}
