<?php

namespace mcms\common\form;

class AjaxActiveForm extends \yii\widgets\ActiveForm implements AjaxActiveFormInterface
{

    use AjaxActiveFormTrait;

    public $enableClientValidation = false;
    public $enableAjaxValidation = true;
}