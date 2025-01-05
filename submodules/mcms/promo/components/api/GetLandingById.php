<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\Landing;
use yii\helpers\Url;

/**
 * api landingById
 * Class GetLandingById
 * @package mcms\promo\components\api
 */
class GetLandingById extends ApiResult
{
  protected $landingId;
  protected $operatorId;

  public function init($params = [])
  {
    $this->landingId = ArrayHelper::getValue($params, 'landingId');

    if (!$this->landingId) $this->addError('landingId is not set');
  }

  public function getResult()
  {
    return Landing::findOne([
      'id' => $this->landingId,
    ]);
  }

  public function getUrlParam()
  {
    return ['/promo/landings/view/', 'id' => $this->landingId];
  }

}