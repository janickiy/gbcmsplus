<?php

namespace mcms\user\components\api;

use mcms\common\module\api\ApiResult;
use mcms\user\components\widgets\ContactFormWidget;
use Yii;

class ContactForm extends ApiResult
{
    private $params;

    function init($params = [])
    {
        $this->params = $params;
    }

    public function getResult()
    {
        $this->prepareWidget(ContactFormWidget::class, $this->params);
        return parent::getResult();
    }

}