<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\join\Query;
use mcms\promo\models\LandingPayType;

class GetLandingPayTypeById extends ApiResult
{
  protected $landingPayTypeId;

  public function init($params = [])
  {
    $this->landingPayTypeId = ArrayHelper::getValue($params, 'landingPayTypeId');

    if (!$this->landingPayTypeId) $this->addError('landingPayTypeId is not set');
  }

  public function getResult()
  {
    return LandingPayType::findOne($this->landingPayTypeId);
  }

  public function join(Query &$query)
  {
    $query
      ->setRightTable(LandingPayType::tableName())
      ->setRightTableColumn('id')
      ->join()
    ;
  }

}