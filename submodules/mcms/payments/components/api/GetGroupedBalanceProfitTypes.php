<?php
namespace mcms\payments\components\api;
use mcms\common\module\api\ApiResult;

use mcms\payments\models\UserBalancesGroupedByDay;

class GetGroupedBalanceProfitTypes extends ApiResult
{
  const TYPE_REBILL = UserBalancesGroupedByDay::TYPE_REBILL;
  const TYPE_ONETIME = UserBalancesGroupedByDay::TYPE_ONETIME;
  const TYPE_BUYOUT = UserBalancesGroupedByDay::TYPE_BUYOUT;
  const TYPE_REFERRAL = UserBalancesGroupedByDay::TYPE_REFERRAL;
  const TYPE_SOLD_TB = UserBalancesGroupedByDay::TYPE_SOLD_TB;

  function init($params = [])
  {
    return $this;
  }
  public function getTypes()
  {
    $groupedBalance = new UserBalancesGroupedByDay();
    return $groupedBalance->getTypes();
  }

  public function getTypeRebill() {
    return self::TYPE_REBILL;
  }

  public function getTypeOnetime() {
    return self::TYPE_ONETIME;
  }

  public function getTypeBuyout() {
    return self::TYPE_BUYOUT;
  }

  public function getTypeReferral() {
    return self::TYPE_REFERRAL;
  }

  public function getTypeSellTrafficback()
  {
    return self::TYPE_SOLD_TB;
  }
}