<?php

namespace mcms\common\exceptions;

use yii\base\Exception;

class ParamRequired extends Exception
{
    protected $paramField;

    public function setParamField($paramField)
    {
        $this->paramField = $paramField;
        return $this;
    }

    public function getName()
    {
        return sprintf('Param field "%s" is required', $this->paramField);
    }
}