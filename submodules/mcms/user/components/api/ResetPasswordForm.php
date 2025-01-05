<?php

namespace mcms\user\components\api;

use mcms\common\module\api\ApiResult;
use mcms\user\components\widgets\ResetPasswordFormWidget;

class ResetPasswordForm extends ApiResult
{
    private $params;

    function init($params = [])
    {
        $this->params = $params;
    }

    public function getResult()
    {
        $this->prepareWidget(ResetPasswordFormWidget::class, $this->params);
        return parent::getResult();
    }

}