<?php

namespace mcms\user\components\api\relations;

use mcms\common\module\api\ApiResult;
use yii\db\ActiveRecord;

class User extends ApiResult
{
    public function hasOne(ActiveRecord $model, $column)
    {
        return $this->hasOneRelation($model, \mcms\user\models\User::class, ['id' => $column]);
    }

    function init($params = [])
    {

    }
}