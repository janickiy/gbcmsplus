<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\models\Source as SourceModel;

class IpList extends ApiResult
{

  public function init($params = array())
  {

  }

  public function getIpFormatRange()
  {
    return SourceModel::IP_FORMAT_RANGE;
  }

  public function getIpFormatCidr()
  {
    return SourceModel::IP_FORMAT_CIDR;
  }

}
