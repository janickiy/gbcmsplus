<?php

namespace mcms\payments\components\widgets;

use mcms\common\helpers\Html;
use Yii;
use yii\base\Widget;
use kartik\widgets\FileInput;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

/**
 * TRICKY Класс во многом дублирует \mcms\payments\models\wallet\WalletForm
 * Соответственно если есть баг, скорее всего он есть и в том классе. Аналогично и для решенией.
 * Перед реализацией, нужно проверить нет ли решения там
 */
class FormBuilder extends Widget
{
  /**
   * @var ActiveForm
   */
  public $form;
  /**
   * @var IFormBuildeModel
   */
  public $model;

  public $fileUploadUrl;
  public $fileDeleteUrl;
  public $allowedExtensions = [];

  public function run()
  {
    return $this->render('form_builder', [
      'model' => $this->model,
      'form' => $this->form,
    ]);
  }

  /**
   * Создает массив отрендеренных в HTML элементов формы для инициализированного типа кошелька
   * @return array
   */
  public function createFormFields($options = [], $submitButtonSelector = '[type="submit"]')
  {
    $result = [];

    $fields = $this->model->getCustomFields($this->form, $options, $submitButtonSelector);

    foreach ($this->model->getFormFields() as $field) {
      if (!array_key_exists($field, $fields)) {
        $result[$field] = $this->form->field($this->model, $field, $options)->textInput(['placeholder' => $this->model->attributePlaceholder($field)]);
      } else {
        $result[$field] = $fields[$field];
      }
    }

    return $result;
  }

  /**
   * Создает массив отрендеренных в HTML элементов формы для инициализированного типа кошелька
   * @return array
   */
  public function createAdminFormFields($options = [])
  {
    $result = [];

    $fields = $this->model->getAdminCustomFields($this->form, $options);

    foreach ($this->model->getAdminFormFields() as $field) {
      if (!array_key_exists($field, $fields)) {
        $result[$field] = $this->form->field($this->model, $field, $options)->textInput(['placeholder' => $this->model->attributePlaceholder($field)]);
      } else {
        $result[$field] = $fields[$field];
      }
    }

    return $result;
  }

  public function checkbox($field, $fieldOptions = [], $options = [], $enclosedByLabel = true)
  {
    $readonly = self::isReadOnly($fieldOptions);

    if ($readonly) {
      $options['disabled'] = true;
    }

    return $this->form->field($this->model, $field, $fieldOptions)->checkbox($options, $enclosedByLabel);
  }

  public function hiddenInput($field, $fieldOptions = [])
  {
    return $this->form->field($this->model, $field, $fieldOptions)->hiddenInput()->label(false);
  }

  public function passwordInput($field, $fieldOptions = [], $options = [])
  {
    return $this->form->field($this->model, $field, $fieldOptions)->passwordInput($options);
  }

  /**
   * @param $field
   * @param $options
   * @param array $widgetOptions {onStartUpload, onEndUpload, uploadUrl, deleteUrl, initialPreview}
   * @return string
   */
  public function fileInput($field, $options, $widgetOptions = [])
  {
    $onStartUpload = isset($widgetOptions['onStartUpload']) ? $widgetOptions['onStartUpload'] : '';
    $onEndUpload = isset($widgetOptions['onEndUpload']) ? $widgetOptions['onEndUpload'] : '';
    $uploadUrl = isset($widgetOptions['uploadUrl']) ? $widgetOptions['uploadUrl'] : '';
    $deleteUrl = isset($widgetOptions['deleteUrl']) ? $widgetOptions['deleteUrl'] : '';
    $initialPreview = isset($widgetOptions['initialPreview']) ? $widgetOptions['initialPreview'] : [];

    $fieldFile = $this->model->formName() . '[' . $field . '_file]';
    $inputName = $this->model->formName() . '[' . $field . ']';

    $readonly = self::isReadOnly($options);

    if (empty($initialPreview) && $this->model->{$field}) {
      $initialPreview = [
        '
        <div class="file-preview-other-frame">
           <div class="file-preview-other">
              <span class="file-icon-4x"><i class="glyphicon glyphicon-file"></i></span>
            </div>
            '.$field.'
        </div>
        '
      ];
    }

    FileInput::$autoIdPrefix = 'widget_';
    return
      Html::activeLabel($this->model, $field).
      FileInput::widget([
        'options' => [
          'id' => Html::getUniqueId()
        ],
        'name'=> $fieldFile,
        'readonly' => $readonly,
        'disabled' => $readonly,
        'pluginOptions' => [
          'allowedFileExtensions' => $this->allowedExtensions,
          'uploadUrl' => $uploadUrl,
          'uploadExtraData' => [
            'formName' => $this->model->formName(),
            'attribute' => $fieldFile,
          ],
          'initialPreviewAsData' => true,
          'initialPreview' => $initialPreview,
          'initialPreviewShowDelete' => true,
          'showClose' => false,
          'showUpload' => false,
          'showRemove' => false,
          'showBrowse' => !$readonly,
          'initialPreviewConfig' => [
            [
              'width' =>  '120px',
              'url' =>  $deleteUrl,
              'key' =>  $field,
            ]
          ],
          'layoutTemplates' => [
            'main1' => '<div class="text-right">{preview}{remove}{cancel}{upload}{browse}</div><br>',
            'main2' => '<div class="text-right">{preview}{remove}{cancel}{upload}{browse}</div><br>',
            'actionDelete' => '<button type="button" class="kv-file-remove {removeClass}" title="{removeTitle}"{dataUrl}{dataKey}>×</button>',

          ]
        ],
        'pluginEvents' => [
          'fileuploaded' => 'function(event, data, previewId, index) {
            $(\'[name="' . $inputName . '"]\').val(data.response.url);
            ' . new JsExpression($onEndUpload) . '
          }',
          'fileselect' => 'function(event, numFiles, label) {
            $(\'[name="' . $fieldFile . '"]\').fileinput("upload");
            ' . new JsExpression($onStartUpload) . '
          }',
          'fileunlock' => 'function(event, filestack, extraData) { 
            ' . new JsExpression($onEndUpload) . '
          }',
          'filepredelete' => "function(event, key) { return (!confirm('" .
            Yii::_t('payments.wallets.remove_file') . "?')); }",
          'filereset' => 'function(event, key) { 
              $(\'[name=\"' . $inputName . '\"]\').val(""); 
              $(\'[name="' . $fieldFile . '"]\').fileinput("clear");
          }',
          'filedeleted' => 'function(event, key) { 
              $(\'[name=\"' . $inputName . '\"]\').val(""); 
              $(\'[name="' . $fieldFile . '"]\').fileinput("clear");
          }',
          'fileuploaderror' => 'function(data, msg) { 
              $(\'[name=\"' . $inputName . '\"]\').val(""); 
              $(\'[name="' . $fieldFile . '"]\').fileinput("clear");
          }',
        ]
      ]) .
      $this->form->field($this->model, $field)->hiddenInput()->label(false);
  }

  private static function isReadOnly($options)
  {
    $readonly = false;
    if (isset($options['inputOptions']['readonly'])) {
      $readonly = (bool)$options['inputOptions']['readonly'];
    }
    return $readonly;
  }
}