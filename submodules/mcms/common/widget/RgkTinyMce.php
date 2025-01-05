<?php

namespace mcms\mcms\common\widget;


use dosamigos\tinymce\TinyMce;
use mcms\common\traits\WidgetUniqueIdTrait;
use yii\web\JsExpression;

/**
 * Виджет TinyMce
 * Переопределил т.к. не отправлялся _csrf
 */
class RgkTinyMce extends TinyMce
{
  use WidgetUniqueIdTrait;

  /**
   * @throws \yii\base\InvalidConfigException
   */
  public function init()
  {
    parent::init();
    $imagesUploadHandlerIsEmpty = empty($this->clientOptions['images_upload_handler']);
    $idIsNotEmpty = !empty($this->options['id']);
    $imagesUploadUrlIsNotEmpty = !empty($this->clientOptions['images_upload_url']);

    if ($imagesUploadHandlerIsEmpty && $idIsNotEmpty && $imagesUploadUrlIsNotEmpty) {
      // без этого костыля не отправляется _csrf
      $this->clientOptions['images_upload_handler'] = new JsExpression("function (blobInfo, success, failure) {
                var xhr, formData;
            
                xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                xhr.open('POST', '" . $this->clientOptions['images_upload_url'] . "');
              
                xhr.onload = function() {
                  var json;
              
                  if (xhr.status < 200 || xhr.status >= 300) {
                    failure('HTTP Error: ' + xhr.status);
                    return;
                  }
              
                  json = JSON.parse(xhr.responseText);
              
                  if (!json || typeof json.location != 'string') {
                    failure(xhr.responseText);
                    return;
                  }
              
                  success(json.location);
                };
              
                formData = new FormData();
                formData.append('_csrf', $('#" . $this->options['id'] . "').closest('form').find('input[name=\'_csrf\']').val());
                formData.append('file', blobInfo.blob(), blobInfo.fileName);
              
                xhr.send(formData);
              }");
    }
  }

}