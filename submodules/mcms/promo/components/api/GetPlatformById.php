<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\models\Platform;

use mcms\common\helpers\ArrayHelper;

/**
 * api platformId
 * Class GetPlatformById
 * @package mcms\promo\components\api
 */
class GetPlatformById extends ApiResult
{
  protected $platformId;

  public function init($params = [])
  {
    $this->platformId = ArrayHelper::getValue($params, 'platformId');

    if (!$this->platformId) $this->addError('platformId is not set');
  }

  public function getResult()
  {
    return Platform::findOne($this->platformId);
  }

  public function getGridViewUrlParam()
  {
    return ['/promo/platforms/index/', 'PlatformSearch[id]' => $this->platformId, 'PlatformSearch[status]' => ''];
  }

}