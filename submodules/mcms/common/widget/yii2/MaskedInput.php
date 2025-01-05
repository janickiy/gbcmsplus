<?php

namespace mcms\common\widget\yii2;

use yii\web\JsExpression;

/**
 * @inheritdoc
 */
class MaskedInput extends \yii\widgets\MaskedInput
{
  /**
   * @inheritdoc
   */
  protected function initClientOptions()
  {
    $options = $this->clientOptions;
    foreach ($options as $key => $value) {
      if (!$value instanceof JsExpression && in_array($key, ['oncomplete', 'onincomplete', 'oncleared', 'onKeyUp',
          'onKeyDown', 'onBeforeMask', 'onBeforePaste', 'onUnMask', 'isComplete', 'determineActiveMasksetIndex', 'onKeyValidation'], true)
      ) {
        $options[$key] = new JsExpression($value);
      }
    }
    if (!isset($options['onKeyValidation'])) {
      $options['onKeyValidation'] = new JsExpression('function (key, result) {
        var $this = $(this);
        if (result === false) {
          $this.addClass("input-validation").delay(1000).queue(function() {
            $(this).removeClass("input-validation").dequeue();
          });
        }
      }');
    }
    $this->clientOptions = $options;
  }
}
