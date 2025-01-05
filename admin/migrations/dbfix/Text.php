<?php

namespace admin\migrations\dbfix;

use kartik\builder\Form;

class Text extends SettingsAbstract
{
    private $validators = [['required'], ['string']];

    protected $type = Form::INPUT_TEXTAREA;

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

    /**
     * @inheritDoc
     */
    public function getFormAttributes()
    {
        $formAttributes = parent::getFormAttributes();
        $formAttributes['options']['rows'] = 4;
        $formAttributes['options']['style'] = 'resize: vertical';
        return $formAttributes;
    }
}