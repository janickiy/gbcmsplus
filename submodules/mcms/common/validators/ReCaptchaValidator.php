<?php

namespace mcms\common\validators;

use mcms\user\components\widgets\recaptcha\ReCaptchaValidator2 as Validator;

/**
 * Валидатор рекапчи. Переопределен для использования в форме регистрации affshark
 */
class ReCaptchaValidator extends Validator
{

  /**
   * Переопределяем клиентскую валидацию рекапчи, т.к. в ленде аффшарка две капчи одновременно в восстановлении
   * пароля и регистрации и на клиенте их нельзя провалидировать
   * @param \yii\base\Model $model
   * @param string $attribute
   * @param \yii\web\View $view
   * @return string
   */
  public function clientValidateAttribute($model, $attribute, $view)
  {
    return null;
  }

}
