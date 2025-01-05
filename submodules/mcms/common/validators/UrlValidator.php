<?php

namespace mcms\common\validators;

use Yii;

/**
 */
class UrlValidator extends \yii\validators\UrlValidator
{
  /**
   * в оригинальном валидаторе проблема с преобразованием в idn_to_ascii(), она возвращает ошибку
   * если длина строки превышена.
   * @inheritdoc
   */
  protected function validateValue($value)
  {
    // make sure the length is limited to avoid DOS attacks
    if (is_string($value) && strlen($value) < 2000) {
      if ($this->defaultScheme !== null && strpos($value, '://') === false) {
        $value = $this->defaultScheme . '://' . $value;
      }

      if (strpos($this->pattern, '{schemes}') !== false) {
        $pattern = str_replace('{schemes}', '(' . implode('|', $this->validSchemes) . ')', $this->pattern);
      } else {
        $pattern = $this->pattern;
      }

      if ($this->enableIDN) {
        $value = Yii::$app->formatter->asIdnToAscii($value);
      }

      if (preg_match($pattern, $value)) {
        return null;
      }
    }

    return [$this->message, []];
  }
}