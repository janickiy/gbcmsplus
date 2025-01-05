<?php

namespace mcms\common\form;

use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;

/**
 * Class ActiveKartikField
 * @package mcms\common\form
 */
class ActiveKartikField extends \kartik\form\ActiveField
{
    const CHECKBOX_DEFAULT_CLASS = 'checkbox';
    const HINT_DEFAULT_CLASS = 'note';

    /**
     * @param array $options
     * @param bool $enclosedByLabel
     * @return \kartik\form\ActiveField
     */
    public function checkbox($options = [], $enclosedByLabel = true)
    {
        $options['class'] = ArrayHelper::getValue($options, 'class', self::CHECKBOX_DEFAULT_CLASS);
        $options['label'] = Html::tag(
            'span',
            ArrayHelper::getValue($options, 'label', $this->model->getAttributeLabel($this->attribute))
        );
        return parent::checkbox($options, $enclosedByLabel);
    }

    /**
     * @param string $content
     * @param array $options
     * @return \kartik\form\ActiveField
     */
    public function hint($content, $options = [])
    {
        $options['class'] = ArrayHelper::getValue($options, 'class', self::HINT_DEFAULT_CLASS);
        return parent::hint($content, $options);
    }

    /**
     * @param array $items
     * @param array $options
     * @return \kartik\form\ActiveField
     */
    public function radioList($items, $options = [])
    {
        $inline = ArrayHelper::remove($options, 'inline', false);
        $options['item'] = function ($index, $label, $name, $checked, $value) use ($inline) {
            $radio = Html::tag(
                'label',
                Html::radio($name, $checked, ['value' => $value, 'class' => 'radiobox']) .
                Html::tag('span', $label)
            );
            return $inline
                ? $radio
                : Html::tag('div', $radio, ['class' => 'radio']);
        };

        return parent::radioList($items, $options);
    }

    /**
     * @param array $items
     * @param array $options
     * @return \kartik\form\ActiveField
     */
    public function checkboxList($items, $options = [])
    {
        $inline = ArrayHelper::remove($options, 'inline', false);
        $options['item'] = function ($index, $label, $name, $checked, $value) use ($inline) {
            $checkbox = Html::tag(
                'label',
                Html::checkbox($name, $checked, ['value' => $value, 'class' => 'checkbox']) .
                Html::tag('span', $label)
            );
            return $inline
                ? $checkbox
                : Html::tag('div', $checkbox, ['class' => 'checkbox']);
        };

        return parent::checkboxList($items, $options);
    }

}