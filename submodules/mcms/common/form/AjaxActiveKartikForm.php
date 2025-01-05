<?php


namespace mcms\common\form;

class AjaxActiveKartikForm extends ActiveKartikForm implements AjaxActiveFormInterface
{
    use AjaxActiveFormTrait;

    public $enableClientValidation = false;
    public $enableAjaxValidation = true;
}