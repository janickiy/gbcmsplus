<?php

namespace mcms\common\exceptions\api;

use yii\base\Exception;

class ClassNameNotDefinedException extends Exception
{

    public $className;

    public function getName()
    {
        return 'Api class name "' . $this->className . '" not defined';
    }

}