<?php

namespace admin\migrations\dbfix;

use kartik\builder\Form;

class Float extends SettingsAbstract
{
    protected $type = Form::INPUT_TEXT;

    public function setValue($value)
    {
        $value = floatval($value);
        return parent::setValue($value);
    }

    /**
     * @inheritDoc
     */
    public function getValidator()
    {
        return [['required'], ['double']];
    }
}