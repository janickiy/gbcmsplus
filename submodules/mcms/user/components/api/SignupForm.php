<?php

namespace mcms\user\components\api;

use mcms\common\module\api\ApiResult;
use mcms\user\components\widgets\SignupFormWidget;

class SignupForm extends ApiResult
{
    private $params;
    function init($params = [])
    {
        $this->params = $params;
    }

    public function getResult()
    {
        $this->prepareWidget(SignupFormWidget::class, $this->params);
        return parent::getResult();
    }
}