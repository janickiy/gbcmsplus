<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\Module;
use Yii;

/**
 * TRICKY Данный функционал на данный момент закоментирован во вьюхах https://rgkdev.atlassian.net/browse/MCMS-1890
 */
class TbEnabled extends ApiResult
{

  /**
   * @inheritdoc
   */
  function init($params = [])
  {
  }

  /**
   * @return bool
   */
  public function isEnabled()
  {
    return !!Module::getInstance()->settings->getValueByKey(Module::SETTINGS_ENABLE_TB_SELL);
  }

}