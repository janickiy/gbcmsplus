<?php
/**
 * @copyright Copyright (c) 2023 VadimTs
 * @link https://tsvadim.dev/
 * Creator: VadimTs
 * Date: 01.11.2023
 */

namespace mcms\partners\components\helpers;

use Yii;
use yii\helpers\ArrayHelper;

class TbReasonsHelper
{
  const REASON_DEFAULT = 1;
  const REASON_OPERATOR_NOT_FOUND = 2;
  const REASON_LANDING_NOT_FOUND = 3;
  const REASON_RESTRICTED_WORDS = 4;
  const REASON_APP_TRAFFIC = 5;
  const REASON_OPERATOR_NOT_ACCEPTED = 6;
  
  public static function getName($type)
  {
    return ArrayHelper::getValue([
      self::REASON_DEFAULT => Yii::_t('statistic.tb_reason_default'),
      self::REASON_OPERATOR_NOT_FOUND => Yii::_t('statistic.operator_not_found'),
      self::REASON_LANDING_NOT_FOUND => Yii::_t('statistic.landing_not_found'),
      self::REASON_RESTRICTED_WORDS => Yii::_t('statistic.restricted_words'),
      self::REASON_APP_TRAFFIC => Yii::_t('statistic.app_traffic'),
      self::REASON_OPERATOR_NOT_ACCEPTED => Yii::_t('statistic.operator_not_accepted')
    ], $type);
  }
}