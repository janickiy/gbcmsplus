<?php

namespace mcms\payments\lib\mgmp;

use mcms\common\helpers\ArrayHelper;
use yii\base\Object;

/**
 * Преобразование кодов платежных систем из MCMS в MGMP и обратно
 */
class PaysystemsCodeCaster extends Object
{
  /** @const string[] Соответствие кодов платежных систем в формате [MCMS => MGMP] */
  const MCMS_MGMP = [
    'card' => 'card',
    'epayments' => 'epayments',
    'juridical-person' => 'juridical_person',
    'paxum' => 'paxum',
    'paypal' => 'paypal',
    'private-person' => 'private_person',
    'qiwi' => 'qiwi',
    'webmoney' => 'webmoney',
    'wireiban' => 'wire_iban',
    'yandex-money' => 'yandex-money',
  ];

  /**
   * Преобразовать Код MCMS в код MGMP
   * @param string $mcms
   * @return string|null
   */
  public static function mcms2mgmp($mcms)
  {
    return ArrayHelper::getValue(static::MCMS_MGMP, $mcms);
  }

  /**
   * Преобразовать Код MGMP в код MCMS
   * @param string $mgmp
   * @return string|null
   */
  public static function mgmp2mcms($mgmp)
  {
    return ArrayHelper::getValue(array_flip(static::MCMS_MGMP), $mgmp);
  }
}