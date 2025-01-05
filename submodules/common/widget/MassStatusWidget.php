<?php

namespace mcms\common\widget;

use rgk\utils\helpers\Html;

class MassStatusWidget extends \yii\base\Widget
{
    public $url = ['mass-activate'];
    public $id;
    public $pjaxId;
    public $label;
    public $confirm;
    public $optionsClass;
    public $buttonClass = 'mass-status-button';


    public function init()
    {
        parent::init();
        !$this->id && $this->id = Html::getUniqueId();

        if (empty($this->label))
            $this->label = \Yii::_t('commonMsg.main.mass-activate-label');

        if (empty($this->confirm))
            $this->confirm = \Yii::_t('commonMsg.main.mass-activate-confirm');

        $this->registerJs();
    }

    public function run()
    {
        return AjaxRequest::widget([
            'title' => $this->label,
            'confirm' => $this->confirm,
            'url' => $this->url,
            'pjaxId' => $this->pjaxId,
            'beforeSubmit' => $this->beforeSubmitJs(),
            'buttonClass' => $this->buttonClass,
            'options' => [
                'class' => $this->optionsClass,
                'id' => $this->id,
                'disabled' => true,
            ],
        ]);
    }

    private function registerJs()
    {
        $js = <<<JS
  $(document).on('change', '[name="selection[]"], .select-on-check-all', function(event) {
  var selection = [];
  $('[name="selection[]"]').each(function(ind, element) {
    var element = $(element);
    if (element.is(":checked")) {
      selection.push(element.val());
    }
  });
  $('.{$this->buttonClass}').attr('disabled', !selection.length);
});
$('{$this->pjaxId}').on('pjax:complete', function() {
    $('.{$this->buttonClass}').attr('disabled', true);
  });
JS;
        $this->view->registerJs($js);
    }

    /**
     * JS, который выполнится перед отправкой данных на сервер
     * @return string
     */
    private function beforeSubmitJs()
    {
        return <<<JS
  var selection = [];
  $('[name="selection[]"]').each(function(ind, element) {
    var element = $(element);
    if (element.is(":checked")) {
      selection.push(element.val());
    }
  });
  var disabled = selection.length < 1;
  if (disabled) return false;
  
  $('#{$this->id}').data('value', JSON.stringify(selection));
JS;
    }
}