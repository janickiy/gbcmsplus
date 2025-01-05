<?php

namespace mcms\partners\components\widgets;

use yii\helpers\Json;
use yii\web\JsExpression;
use Yii;


class FileApiWidget extends \vova07\fileapi\Widget
{
  const IMAGE_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif'];
  public $cropModal = true;
  // TRICKY Пока что поддерживается только в партнерке. Есть задача для внедрения в админку
  public $extensions = [];
  protected $showCropBlockJs;
  protected $hideCropBlockJs;
  protected $cropBlockOnSelectJs;

  public function init()
  {
    if ($this->cropModal) {
      $this->showCropBlockJs = new JsExpression('$("#modal-crop").modal("show");');
      $this->hideCropBlockJs = new JsExpression('$("#modal-crop").modal("hide");');
      $this->cropBlockOnSelectJs = new JsExpression(<<<JS
        var contentWindth = jQuery(window).width() - 100; 
        if(info.width < jQuery(window).width()) contentWindth = info.width;
        if(contentWindth < minWindth) contentWindth = minWindth;
        $("#modal-crop").find(".modal-dialog").css({width:  contentWindth + paddingWidth + "px"});
JS
      );
    } else {
      $this->showCropBlockJs = new JsExpression('$("#modal-crop").collapse("show");');
      $this->hideCropBlockJs = new JsExpression('$("#modal-crop").collapse("hide");');
    }

    parent::init();
  }

  /**
   * Register default widget callbacks
   */
  protected function registerDefaultCallbacks()
  {
    // File complete handler
    $this->callbacks['filecomplete'][] = new JsExpression('function (evt, uiEvt) {' .
      'if (uiEvt.result.error) {' .
      'alert(uiEvt.result.error);' .
      '} else {' .
      'jQuery(this).find("input[type=\"hidden\"]").val(uiEvt.result.name);' .
      'jQuery(this).find("[data-fileapi=\"browse-text\"]").addClass("hidden");' .
      'jQuery(this).find("[data-fileapi=\"delete\"]").attr("data-fileapi-uid", FileAPI.uid(uiEvt.file));' .
      '}' .
      '}');

    if ($this->crop === true) {
      $view = $this->getView();
      $selector = $this->getSelector();
      $jcropSettings = Json::encode($this->jcropSettings);

      if ($this->cropResizeWidth !== null && $this->cropResizeHeight !== null) {
        $cropResizeJs = "el.fileapi('resize', ufile, $this->cropResizeWidth, $this->cropResizeHeight);";
      } elseif ($this->cropResizeWidth !== null && $this->cropResizeHeight == null) {
        $cropResizeJs = "el.fileapi('resize', ufile, $this->cropResizeWidth, ((coordinates.h * $this->cropResizeWidth)/coordinates.w));";
      } elseif ($this->cropResizeWidth == null && $this->cropResizeHeight !== null) {
        $cropResizeJs = "el.fileapi('resize', ufile, ((coordinates.w * $this->cropResizeHeight)/coordinates.h), $this->cropResizeHeight);";
      } elseif ($this->cropResizeMaxWidth !== null && $this->cropResizeMaxHeight !== null) {
        $cropResizeJs = "if(coordinates.w > $this->cropResizeMaxWidth) el.fileapi('resize', ufile, $this->cropResizeMaxWidth, ((coordinates.h * $this->cropResizeMaxWidth)/coordinates.w));";
        $cropResizeJs .= "else if(coordinates.h > $this->cropResizeMaxHeight) el.fileapi('resize', ufile, ((coordinates.w * $this->cropResizeMaxHeight)/coordinates.h), $this->cropResizeMaxHeight);";
      } elseif ($this->cropResizeMaxWidth !== null && $this->cropResizeMaxHeight == null) {
        $cropResizeJs = "if(coordinates.w > $this->cropResizeMaxWidth) el.fileapi('resize', ufile, $this->cropResizeMaxWidth, ((coordinates.h * $this->cropResizeMaxWidth)/coordinates.w));";
      } elseif ($this->cropResizeMaxWidth == null && $this->cropResizeMaxHeight !== null) {
        $cropResizeJs = "if(coordinates.h > $this->cropResizeMaxHeight) el.fileapi('resize', ufile, ((coordinates.w * $this->cropResizeMaxHeight)/coordinates.h), $this->cropResizeMaxHeight);";
      } else {
        $cropResizeJs = '';
      }
      $view->registerJs(/** @lang JavaScript */
        '$("#modal-crop").on("hide.bs.modal", function() { 
          // TRICKY Upload вызывается отдельно при закрытии модалки и при нажатии на кнопку Загрузить, 
          // потому что кроме модалки ава может загружаться через Bootstrap collapse
          if ($("#modal-crop").data("crop-saved") !== true)$("#' . $selector . '").fileapi("upload");
        });
');
      // Add event handler for crop button
      $view->registerJs('jQuery(document).on("click", "#modal-crop .crop", function() {' .
        '
        $("#modal-crop").data("crop-saved", true);
        $("#' . $selector . '").fileapi("upload");' .
        $this->hideCropBlockJs .
        '$("#modal-preview").html("");' .
        //Делаем превью загруженного файла
        '$("#' . $selector . '").fileapi("crop", $("#modal-crop").data("crop-file"), $("#modal-crop").data("crop-coordinates"));
        });');
      // Crop event handler
      $this->callbacks['select'] = new JsExpression('function (evt, ui) {' .
        'var ufile = ui.files[0],' .
        'jcropSettings = ' . $jcropSettings . ',' .
        'el = jQuery(this),
         contentWindth,
         minWindth = 150,
         paddingWidth = 30;' .
        'if (ufile) {' .
        ($this->extensions ? 'var fileExtension = ufile.name.split(".").slice(-1)[0];
          if ($.inArray(fileExtension, '.json_encode($this->extensions).') == -1) {
             notifyInit(null, "'.Yii::_t('partners.main.invalid_file_type', ['extensions' => implode(', ', $this->extensions)]).'", false);
             return false;
          }' : null)
          . 'jcropSettings.file = ufile;' .
        'jcropSettings.onSelect = function (coordinates) {' .
          '$("#modal-crop").data("crop-file", ufile);
          $("#modal-crop").data("crop-coordinates", coordinates);' .
        '};' .
        'FileAPI.getInfo (ufile, function (err, info) {
            '.$this->cropBlockOnSelectJs.'
            if (!err) { var coordinates = { w: info.width, h: info.height }; ' . $cropResizeJs . '}' .
        '});
          if (typeof jcropSettings["maxSize"] == "undefined") {
            jcropSettings["maxSize"] = [jQuery(window).width() - 100, jQuery(window).height() - 100];
          }
          ' . $this->showCropBlockJs
            . 'setTimeout(function () {' .
              '$("#modal-preview").cropper(jcropSettings);' .
            '}, 700);' .
        '}' .
      '}');
    }
  }
}
