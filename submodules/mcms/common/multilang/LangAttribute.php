<?php

namespace mcms\common\multilang;

use Yii;

/**
 * Class LangAttribute - создает мультиязычный объект
 * a если ожидается строка то отдает значение на нужном языке
 * @package mcms\common\multilang
 */
class LangAttribute
{

    public function __construct($attribute)
    {
        $attribute = unserialize($attribute);

        if (is_object($attribute)) $attribute = (array)$attribute;
        if (!is_array($attribute)) return;

        foreach ($attribute as $key => $value) {
            $this->$key = $value;
        }
    }

    public function __toString()
    {
        return $this->getCurrentLangValue();
    }

    public function getLangValue($lang)
    {
        if (!property_exists($this, $lang)) return "";
        return $this->{$lang};
    }

    public function getCurrentLangValue()
    {
        $propertyName = Yii::$app->language;

        return $this->getLangValue($propertyName);
    }
}