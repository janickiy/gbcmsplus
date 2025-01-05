<?php

namespace mcms\partners\components\subidStat;


class Group extends \mcms\statistic\components\mainStat\Group
{
  const BY_SUBID1 = 'subid1';
  const BY_SUBID2 = 'subid2';
  const BY_SUBID12 = 'subid12';

  /**
   * Получить доступные юзеру группировки
   * @return array
   */
  public static function getAvailableGroups()
  {
    return [
      self::BY_SUBID1,
      self::BY_SUBID2,
      self::BY_SUBID12,
    ];
  }
}