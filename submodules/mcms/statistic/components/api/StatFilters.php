<?php

namespace mcms\statistic\components\api;

use mcms\statistic\models\mysql\StatFilter;
use Yii;
use mcms\common\module\api\ApiResult;

/**
 * Фильтры статистики
 * Class StatFilters
 * @package mcms\statistic\components\api
 */
class StatFilters extends ApiResult
{
  /**
   * @inheritdoc
   */
  function init($params = [])
  {
  }

  public function filterOperators(&$query, $userId)
  {
    return StatFilter::filterOperators($query, $userId);
  }
  public function filterStreams(&$query, $userId)
  {
    return StatFilter::filterStreams($query, $userId);
  }
  public function filterLandingOperator(&$query, $userId)
  {
    return StatFilter::filterLandingOperator($query, $userId);
  }
  public function filterLandings(&$query, $userId)
  {
    return StatFilter::filterLandings($query, $userId);
  }
  public function filterPayType(&$query, $userId)
  {
    return StatFilter::filterPayType($query, $userId);
  }
  public function filterPlatforms(&$query, $userId)
  {
    return StatFilter::filterPlatforms($query, $userId);
  }
  public function filterProviders(&$query, $userId)
  {
    return StatFilter::filterProviders($query, $userId);
  }
  public function filterSources(&$query, $userId)
  {
    return StatFilter::filterSources($query, $userId);
  }
  public function filterCountries(&$query, $userId)
  {
    return StatFilter::filterCountries($query, $userId);
  }
}
