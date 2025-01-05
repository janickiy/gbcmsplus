<?php
namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\models\Landing;
use yii\helpers\ArrayHelper;

/**
 * Class LandingsDropdownWidget
 * @package mcms\promo\components\api
 */
class GetLandingsByCategory extends ApiResult
{
  private $isActive;
  private $landingsId = [];
  private $filterName = '';
  private $cache = true;

  public function init($params = [])
  {
    $this->isActive = ArrayHelper::getValue($params, 'isActive', true);
    $this->landingsId = ArrayHelper::getValue($params, 'landingsId', []);
    $this->filterName = ArrayHelper::getValue($params, 'filterName', '');
    $this->cache = ArrayHelper::getValue($params, 'cache', true);
  }

  public function getResult()
  {
    return Landing::getLandingsByCategory($this->isActive, $this->landingsId, $this->filterName, $this->cache);
  }
}