<?php

namespace mcms\loyalty\models;

use yii\base\Object;

/**
 * TRICKY Класс не нужен на MCMS, интересуют только константы
 * Создан только что бы было удобнее синхронизировать код с MGMP
 */
class LoyaltyBonusProcess extends Object
{
  /** @const string Прошлый месяц */
  const LAST_MONTH = 'lastMonth';
  /** @const string Позапрошлый месяц */
  const BEFORE_LAST_MONTH = 'beforeLastMonth';
  /** @const string Позапозапрошлый месяц */
  const THREE_MONTH_AGO = 'threeMonthAgo';
}