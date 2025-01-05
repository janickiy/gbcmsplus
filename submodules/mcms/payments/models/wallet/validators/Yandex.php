<?php

namespace mcms\payments\models\wallet\validators;

use yii\validators\EmailValidator;
use yii\validators\NumberValidator;
use yii\validators\Validator;
use yii\web\JsExpression;

class Yandex extends Validator
{
  public function init()
  {
    parent::init();

  }


  public function validateAttribute($model, $attribute)
  {
    $value = $model->$attribute;

    $emailValidator = Validator::createValidator(EmailValidator::class, $model, [$attribute]);
    $numberValidator = Validator::createValidator(NumberValidator::class, $model, [$attribute]);
    if (
      !$emailValidator->validate($value) &&
      !$numberValidator->validate($value)
    ) {
      $this->addError($model, 'wallet', $this->message);
    }
  }

  public function clientValidateAttribute($model, $attribute, $view)
  {
    $message = json_encode($this->message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $integerPattern = new JsExpression('/^\s*[+-]?\d+\s*$/');
    return <<<JS
var validateEmail = function(value) {
    var valid = true;

    var regexp = /^((?:"?([^"]*)"?\s)?)(?:\s+)?(?:(<?)((.+)@([^>]+))(>?))$/,
        matches = regexp.exec(value);

    if (matches === null) {
        valid = false
    } else {
        if (matches[5].length > 64) {
            valid = false;
        } else if ((matches[5] + '@' + matches[6]).length > 254) {
            valid = false;
        }
    }

    return valid;
};
if (!value.match($integerPattern) && !validateEmail(value)) {
    messages.push($message);
}
JS;
  }
}