<?php
namespace mcms\pages\components\api;

use mcms\common\module\api\ApiResult;
use mcms\pages\models\PartnerCabinetStyle;
use Yii;

/**
 * 
 * Class PartnerCabinetStyleApi
 * @package mcms\pages\components\api
 */
class PartnerCabinetStyleApi extends ApiResult
{
  public function init($params = []){}

  /**
   * @return PartnerCabinetStyle
   */
  public function getActive()
  {
    return PartnerCabinetStyle::find()->where(['status' => PartnerCabinetStyle::STATUS_ACTIVE])->one();
  }
}