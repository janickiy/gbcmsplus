<?php

namespace mcms\common\module\api\join;

use yii\base\Exception;

class InvalidRightTableFieldsException extends Exception
{
    public function getName()
    {
        return 'rightTableFields array mast be key-value, where keys is alias of column';
    }

}