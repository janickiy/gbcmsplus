<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\models\Country;
use mcms\promo\models\Landing;
use mcms\promo\models\Operator;
use mcms\promo\models\Platform;
use mcms\promo\models\Source as SourceModel;
use mcms\promo\models\Stream;

class GetUrl extends ApiResult
{

  public function init($params = [])
  {
    
  }

  public function viewStream($id = null)
  {
    if (!$id) return null;
    return Stream::getViewUrl($id);
  }

  public function viewSource($id = null, $sourceType = null)
  {
    if (!$id) return null;
    return SourceModel::getViewUrl($id, $sourceType);
  }

  public function viewOperator($id = null)
  {
    if (!$id) return null;
    return Operator::getViewUrl($id);
  }

  public function viewPlatform($id = null)
  {
    if (!$id) return null;
    return Platform::getViewUrl($id);
  }

  public function viewCountry($id = null)
  {
    if (!$id) return null;
    return Country::getViewUrl($id);
  }

  public function viewLanding($id = null)
  {
    if (!$id) return null;
    return Landing::getViewUrl($id);
  }
}