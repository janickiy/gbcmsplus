<?php

namespace admin\migrations\dbfix;

use kartik\builder\Form;

class Integer extends SettingsAbstract
{
    protected $type = Form::INPUT_TEXT;

    public function setValue($value)
    {
        $value = (int)$value;
        return parent::setValue($value);
    }

    /**
     * @inheritDoc
     */
    public function getValidator()
    {
        return [['required'], ['integer']];
    }
}