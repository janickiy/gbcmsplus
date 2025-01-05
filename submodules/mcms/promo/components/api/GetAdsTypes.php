<?php

namespace mcms\promo\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\promo\models\AdsType;
use mcms\promo\models\Source;

/**
 * Class GetAdsTypes
 * @package mcms\promo\components\api
 */
class GetAdsTypes extends ApiResult
{

  /**
   * @inheritdoc
   */
  public function init($params = [])
  {

  }

  /**
   * @inheritdoc
   */
  public function getResult()
  {
    return AdsType::findAll(['status' => AdsType::STATUS_ACTIVE]);
  }

  /**
   * @return null | AdsType
   */
  public function getDefault()
  {
    foreach($this->getResult() as $adsType) {
      if ($adsType->is_default) return $adsType;
    }
    return null;
  }

}


