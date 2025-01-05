<?php

namespace mcms\user\components\api;

use mcms\common\module\api\ApiResult;
use mcms\user\components\widgets\LoginFormWidget;
use Yii;

class LoginForm extends ApiResult
{
    private $params;

    function init($params = [])
    {
        $this->params = $params;
    }

    public function getResult()
    {
        $this->prepareWidget(LoginFormWidget::class, $this->params);
        return parent::getResult();
    }
}