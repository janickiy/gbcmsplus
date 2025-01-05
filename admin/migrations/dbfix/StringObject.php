<?php

namespace admin\migrations\dbfix;

use kartik\builder\Form;

class StringObject extends SettingsAbstract
{
    private $validators = [['required'], ['string']];

    protected $type = Form::INPUT_TEXT;

    /**
     * @inheritDoc
     */
    public function getValidator()
    {
        return $this->validators;
    }

    /**
     * @param array $validators
     * @return $this
     */
    public function setValidators(array $validators)
    {
        $this->validators = $validators;
        return $this;
    }
}
