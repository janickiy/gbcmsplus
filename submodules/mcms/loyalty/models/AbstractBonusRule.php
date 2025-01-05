<?php
namespace mcms\loyalty\models;

use yii\base\Model;
use yii\base\NotSupportedException;

/**
 * Базовый класс для правил бонусов
 * TRICKY Из MGMP перенесен только набор полей, остальное в MCMS не нужно
 */
abstract class AbstractBonusRule extends Model
{
  /** @var integer */
  public $id;
  /** @var float */
  public $amount;
  /** @var float */
  public $percent;

  /**
   * Код типа бонуса.
   * TRICKY Максимальная длина VARCHAR(32)
   * @see LoyaltyBonus::$type
   * @return string
   * @throws NotSupportedException
   */
  public static function getCode()
  {
    throw new NotSupportedException('Не реализован обязательный метод getCode()');
  }
}