<?php
namespace mcms\pages\components\api;

use mcms\common\module\api\ApiResult;

class PagesWidget extends ApiResult
{

  public function init($params = [])
  {
    $this->prepareWidget(\mcms\pages\components\widgets\PagesWidget::class, $params);
  }

}