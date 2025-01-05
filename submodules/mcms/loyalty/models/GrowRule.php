<?php

namespace mcms\loyalty\models;

/**
 * Модель для правила по РОСТУ оборота в программе лояльности
 * TRICKY Из MGMP перенесен только набор полей, остальное в MCMS не нужно
 *
 * @see LoyaltyBonusDetails::$turnoverGrowBonusRule
 */
class GrowRule extends AbstractBonusRule
{
  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'amount' => 'Turnover Increase',
      'percent' => 'Bonus',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ];
  }

  /**
   * @inheritdoc
   */
  public static function getCode()
  {
    return 'grow';
  }
}