<?php

namespace admin\migrations\dbfix;

use kartik\builder\Form;
use Yii;

class Lists extends Options
{
    protected $type = Form::INPUT_DROPDOWN_LIST;

    protected function getLabel($label)
    {
        return Yii::_t($label);
    }
}