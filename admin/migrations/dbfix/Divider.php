<?php

namespace admin\migrations\dbfix;

use kartik\builder\Form;

class Divider extends SettingsAbstract
{
    protected $type = Form::INPUT_RAW;

    public function getValidator()
    {
        return [];
    }
}