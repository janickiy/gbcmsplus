<?php

namespace mcms\partners\components\api;

use mcms\common\module\api\ApiResult;
use mcms\partners\components\widgets\EmailTemplateWidget;
use Yii;

class EmailTemplate extends ApiResult
{
  function init($params = [])
  {
    $this->prepareWidget(EmailTemplateWidget::class, $params);
  }
}