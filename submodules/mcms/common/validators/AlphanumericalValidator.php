<?php

namespace mcms\common\validators;

use yii\validators\RegularExpressionValidator;

class AlphanumericalValidator extends RegularExpressionValidator
{

    const BOTH_REGISTERS = "/^[a-z0-9]+$/ui";
    const ONLY_LOWER = "/^[a-z0-9]+$/u";
    const ONLY_UPPER = "/^[A-Z0-9]+$/u";

    /**
     *
     * @inheritdoc
     */
    public $pattern = self::BOTH_REGISTERS;

}