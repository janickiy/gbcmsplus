<?php

namespace mcms\common\form;

use mcms\common\widget\alert\Alert;
use mcms\common\traits\WidgetUniqueIdTrait;
use Yii;
use mcms\common\hakimel\HakimelAsset;

/**
 * TRICKY Если ajax-форма используется в модальном окне в списке, крайне рекомендуется не указывать параметр id, что бы
 * виджет автоматически сгенерировал уникальный идентификатор в рамках сессии, иначе во всех модальных окнах будет форма
 * с одинаковым идентификатром и могут быть конфликты
 */
trait AjaxActiveFormTrait
{
    use WidgetUniqueIdTrait;

    public $ajaxSuccess;
    public $ajaxError;
    public $ajaxBeforeSend;
    public $ajaxComplete;

    /**
     * Необходимо для выбора библиотеки уведомлений.
     * По умолчанию - $.smallBox(), иначе - $.notify()
     * @var bool
     */
    private $usePartnerScripts = false;

    /**
     * @var bool|null Принудительно отображать уведомления о результате отправки формы.
     * true - уведомление будет отображено даже если ajaxSuccess/ajaxError переопределены
     * false - уведомление будет отображено только если ajaxSuccess/ajaxError не переопределены
     * Если уведомление дублируется, поставьте false или удалите отображение уведомления из ajaxSuccess/Error
     */
    public $forceResultMessages = true;

    /** @var string|null Кастомное сообщение об успехе */
    public $messageSuccess;

    /** @var string|null Кастомное сообщение об ошибке */
    public $messageFail;

    /** @var bool Отображать сообщение об успехе */
    public $showMessageSuccess = true;

    /** @var bool Отображать сообщение об ошибке */
    public $showMessageFail = true;

    public function init()
    {
        $this->options[self::AJAX_FORM_ATTRIBUTE] = true;


        if (empty($this->messageSuccess)) {
            $this->messageSuccess = Yii::_t('app.common.Saved successfully');
        }

        if (empty($this->messageFail)) {
            $this->messageFail = Yii::_t('app.common.Save failed');
        }

        parent::init();

        $this->registerClientFunctions();
    }

    /**
     * Для партнерки задается в true в файле site/config/bootstrap.php
     *
     * @param $value
     */
    public function setUsePartnerScripts($value)
    {
        $this->usePartnerScripts = boolval($value);
    }

    public function registerClientFunctions()
    {
        $jsCode = file_get_contents(
            __DIR__ . DIRECTORY_SEPARATOR .
            'assets' . DIRECTORY_SEPARATOR .
            'js' . DIRECTORY_SEPARATOR .
            'ajax-form.js'
        );

        // Код для отображения флешки
        $alertSuccess = $this->usePartnerScripts === false
            ? Alert::success($this->messageSuccess)
            : 'notifyInit("", "' . $this->messageSuccess . '", true);';
        if (!$this->showMessageSuccess) $alertSuccess = null;

        $alertFail = $this->usePartnerScripts === false
            ? Alert::danger($this->messageFail)
            : 'notifyInit("", event.error ? event.error : "' . $this->messageFail . '", false);';
        if (!$this->showMessageFail) $alertFail = null;

        if (!$this->ajaxSuccess || $this->forceResultMessages) {
            // Отображение флешки
            $this->ajaxSuccess = '
        function(event, jqXHR, ajaxOptions, data){
          if (typeof event.success == "undefined" || event.success == true) {
          ' . $alertSuccess . '
          } else {
          ' . $alertFail . '
          }'
                . (!empty($this->ajaxSuccess) ? 'return (' . $this->ajaxSuccess . ')(event, jqXHR, ajaxOptions, data);' : null)
                . '}';
        }

        $this->ajaxSuccess = 'function(event, jqXHR, ajaxOptions, data){
      var $modal = $("#' . $this->getId() . '").closest(".modal");
      if ($modal.length > 0) {
        $modal.off("hide.bs.modal.prevent");
      }
      return (' . $this->ajaxSuccess . ')(event, jqXHR, ajaxOptions, data);
    }';

        $alertError = 'var message = "' . $this->messageFail . '";
          if (typeof response.responseJSON.error != "undefined" && response.responseJSON.error != "") {
    message = message + "<p>" + response.responseJSON.error + "</p>";
  }
          ' . ($this->usePartnerScripts === false
                ? '$.smallBox({
              "color": "rgb(196, 106, 105)",
              "timeout" : 4000,
              "title": message,
              "sound": false,
              "iconSmall": "miniPic fa fa-warning shake animated"
            });'
                : 'notifyInit("", "' . Yii::_t('app.common.Save failed') . '", false);');
        if (!$this->showMessageFail) $alertError = null;

        if (!$this->ajaxError || $this->forceResultMessages) {
            $this->ajaxError = '
        function(response, jqXHR, ajaxOptions, data){
          if (response.status == 302) return true;'
                . $alertError
                . (!empty($this->ajaxError) ? 'return (' . $this->ajaxError . ')(response, jqXHR, ajaxOptions, data);' : null)
                . '}';
        }

        $this->ajaxError = 'function(event, jqXHR, ajaxSettings, thrownError){
      var $modal = $("#' . $this->getId() . '").closest(".modal");
      if ($modal.length > 0) {
        $modal.off("hide.bs.modal.prevent");
      }
      return (' . $this->ajaxError . ')(event, jqXHR, ajaxSettings, thrownError);
    }';

        if (!$this->ajaxBeforeSend) {
            $this->ajaxBeforeSend = 'function(){}';
        }

        if (!$this->ajaxComplete) {
            $this->ajaxComplete = 'function(){}';
        }
        $this->ajaxComplete = 'function(event, jqXHR, ajaxOptions){
      var $submitButton = $("#' . $this->getId() . '")
        .css({"position":"static"})
        .find("button")
        .filter("[type=submit]")
        .first()
        .ladda()
        .ladda("stop")
      ;
      $("#form-blocker-' . $this->getId() . '").remove();
      return (' . $this->ajaxComplete . ')(event, jqXHR, ajaxOptions);
    }';

        $jsCode = str_replace(self::AJAX_COMPLETE_REPLACE, $this->ajaxComplete, $jsCode);
        $jsCode = str_replace(self::AJAX_BEFORE_SEND_REPLACE, $this->ajaxBeforeSend, $jsCode);
        $jsCode = str_replace(self::AJAX_SUCCESS_REPLACE, $this->ajaxSuccess, $jsCode);
        $jsCode = str_replace(self::AJAX_ERROR_REPLACE, $this->ajaxError, $jsCode);
        $jsCode = str_replace(self::FORM_ID_REPLACE, $this->id, $jsCode);

        $this->getView()->registerJs($jsCode);
        $this->getView()->registerAssetBundle(HakimelAsset::class);
    }
}