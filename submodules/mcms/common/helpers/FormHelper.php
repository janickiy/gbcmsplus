<?php

namespace mcms\common\helpers;

use yii\base\Model;

class FormHelper
{
    public static function validate($model, $attributes = null, $attributePrefix = '')
    {
        $result = [];
        if ($attributes instanceof Model) {
            // validating multiple models
            $models = func_get_args();
            $attributes = null;
        } else {
            $models = [$model];
        }
        /* @var $model Model */
        foreach ($models as $model) {
            $model->validate($attributes);
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[$attributePrefix . '-' . strtolower($attribute)] = $errors;
            }
        }

        return $result;
    }
}