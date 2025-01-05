<?php

namespace common\components\recaptcha;

class ReCaptchaValidator extends \himiklab\yii2\recaptcha\ReCaptchaValidator
{
    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     * @param \yii\web\View $view
     * @return string|null
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        return null;
    }
}