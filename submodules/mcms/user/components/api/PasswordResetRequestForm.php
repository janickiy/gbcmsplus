<?php

namespace mcms\user\components\api;

use mcms\common\module\api\ApiResult;
use mcms\user\components\widgets\PasswordResetRequestFormWidget;
use Yii;

class PasswordResetRequestForm extends ApiResult
{
    private $params;

    function init($params = [])
    {
        $this->params = $params;
    }

    public function getResult()
    {
        $this->prepareWidget(PasswordResetRequestFormWidget::class, $this->params);
        return parent::getResult();
    }
}