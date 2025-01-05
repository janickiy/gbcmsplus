<?php

namespace mcms\common\event;

use yii\helpers\BaseInflector;

class Replacement
{
    public $class;
    private $attributes;
    public function __construct($class, array $attributes)
    {
        $this->class = $class;
        $this->attributes = $attributes;
    }

    public function getArray($fieldName)
    {
        $replacements = [];
        foreach ($this->attributes as $attribute) {
            $getterFunctionResult = $this->getGetterFunctionResult($attribute);
            $fieldAttribute = $fieldName . '.' . $attribute;
            if ($getterFunctionResult instanceof Replacement) {
                $replacements = array_merge($replacements, $getterFunctionResult->getArray($fieldAttribute));
                $replacements[$fieldAttribute] = $getterFunctionResult->class;
            } else {
                $replacements[$fieldAttribute] = $getterFunctionResult;
            }
        }
        return $replacements;
    }

    private function getGetterFunctionResult($attributeName)
    {
        if (!$this->classHasFunction($attributeName)) return null;

        $getterFunction = $this->getterFunction($attributeName);
        return $this->class->$getterFunction();
    }

    private function getterFunction($attributeName)
    {
        return sprintf('getReplacement%s', BaseInflector::camelize($attributeName));
    }

    private function classHasFunction($attributeName)
    {
        return method_exists($this->class, $this->getterFunction($attributeName));
    }
}