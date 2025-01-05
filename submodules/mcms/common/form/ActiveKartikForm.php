<?php

namespace mcms\common\form;

class ActiveKartikForm extends \kartik\form\ActiveForm
{
    public $fieldClass = 'mcms\common\form\ActiveKartikField';

    public function init()
    {
        if (!isset($this->fieldConfig['class'])) {
            $this->fieldConfig['class'] = $this->fieldClass;
        }
        $this->disableSubmitButtonAfterSubmit();
        parent::init();
    }

    /**
     * Дисейблим кнопку Submit перед валидацией формы и возвращаем, если валидация не прошла и форма не отправлена
     */
    private function disableSubmitButtonAfterSubmit()
    {
        $js = <<<JS
    $("#{$this->id}").on('afterValidate', function (event, messages, deferreds) {
      if (deferreds.length > 0) {
        $('input[type=submit]').prop("disabled", false);
      }
    });
    $("#{$this->id}").on('beforeValidate', function () {
        $('input[type=submit]').prop("disabled", true);
    });
JS;

        $this->getView()->registerJs($js);
    }
}