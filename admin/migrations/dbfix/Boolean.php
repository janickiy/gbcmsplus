<?php

namespace admin\migrations\dbfix;

use kartik\builder\Form;

class Boolean extends SettingsAbstract
{
    protected $type = Form::INPUT_CHECKBOX;
    protected $options = ['class' => 'checkbox'];

    public function setValue($value)
    {
        $value = boolval($value);
        return parent::setValue($value);
    }

    public function getFormAttributes()
    {
        return array_merge(parent::getFormAttributes(), [
            'label' => '<span>' . $this->getName() . '</span>',
            'options' => $this->getOptions(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getValidator()
    {
        return [['required'], ['boolean']];
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }
}