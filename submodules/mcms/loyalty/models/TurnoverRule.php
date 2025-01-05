<?php

namespace mcms\loyalty\models;

/**
 * Модель для правила по РАЗМЕРУ оборота в программе лояльности
 * TRICKY Из MGMP перенесен только набор полей, остальное в MCMS не нужно
 *
 * @see LoyaltyBonusDetails::$turnoverRule
 */
class TurnoverRule extends AbstractBonusRule
{
  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'amount' => 'Turnover Amount',
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
    return 'turnover';
  }
}