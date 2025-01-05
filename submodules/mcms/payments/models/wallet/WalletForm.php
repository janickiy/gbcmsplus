<?php

namespace mcms\payments\models\wallet;

use kartik\date\DatePicker;
use kartik\widgets\FileInput;
use mcms\common\helpers\ArrayHelper;
use mcms\common\widget\yii2\MaskedInput;
use Yii;
use yii\base\Object;
use mcms\common\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/**
 * Класс описывающий возможные поля формы кошельков
 * TRICKY Класс во многом дублирует \mcms\payments\components\widgets\FormBuilder
 * Соответственно если есть баг, скорее всего он есть и в том классе. Аналогично и для решенией.
 * Перед реализацией, нужно проверить нет ли решения там
 */
class WalletForm extends Object
{
  const FILE_FIELD_POSTFIX = '_file';
  /**
   * @var ActiveForm
   */
  public $form;
  /**
   * @var AbstractWallet
   */
  public $wallet;

  /**
   * @var int
   */
  public $userWalletId;

  /**
   * Создает массив отрендеренных в HTML элементов формы для инициализированного типа кошелька
   * @return array
   */
  public function createFormFields($options = [], $submitButtonSelector = '[type="submit"]')
  {
    $result = [];

    $fields = $this->wallet->getCustomFields($this->form, $options, $submitButtonSelector);
    
    foreach ($this->wallet->getFormFields() as $field) {
      if (!array_key_exists($field, $fields)) {
        $result[$field] = $this->form->field($this->wallet, $field, $options)->textInput(['placeholder' => $this->wallet->attributePlaceholder($field)]);
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

    $fields = $this->wallet->getAdminCustomFields($this->form, $options);

    foreach ($this->wallet->getAdminFormFields() as $field) {
      if (!array_key_exists($field, $fields)) {
        $result[$field] = $this->form->field($this->wallet, $field, $options)->textInput(['placeholder' => $this->wallet->attributePlaceholder($field)]);
      } else {
        $result[$field] = $fields[$field];
      }
    }

    return $result;
  }

  /**
   * @param $field
   * @param $fieldOptions
   * @param $options
   * @return string
   */
  public function textInput($field, $fieldOptions = [], $options = [])
  {
    $readonly = self::isReadOnly($fieldOptions);

    if ($readonly) {
      $options['disabled'] = true;
    }

    $options['placeholder'] = ArrayHelper::getValue(
      $options,
      'placeholder',
      $this->wallet->attributePlaceholder($field)
    );

    return $this->form->field($this->wallet, $field, $fieldOptions)->textInput($options);
  }


  /**
   *
   * @param string $field атрибут для ActiveField
   * @param array $fieldOptions опции ActiveField
   * @param array $options html опции самого чекбокса
   * @param bool $enclosedByLabel обернут ли чекбокс в label
   * @return string отрендереный чекбокс
   */
  public function checkbox($field, $fieldOptions = [], $options = [], $enclosedByLabel = true)
  {
    $readonly = self::isReadOnly($fieldOptions);

    if ($readonly) {
      $options['disabled'] = true;
    }

    if (!isset($fieldOptions['options'])) $fieldOptions['options'] = [];
    if (!isset($fieldOptions['options']['class'])) $fieldOptions['options']['class'] = null;
    $fieldOptions['options']['class'] .= ' input-checkbox';

    return $this->form->field($this->wallet, $field, $fieldOptions)->checkbox($options, $enclosedByLabel);
  }

  /**
   *
   * @param string $field ActiveField attribute
   * @param array $fieldOptions ActiveField options
   * @param array $options html radioList options
   * @param array $items radioList items
   * @return string render radioList
   */
  public function radioList($field, $items, $options = [], $fieldOptions = [])
  {
    return $this->form->field($this->wallet, $field, $fieldOptions)->radioList($items, array_merge([
      'item' => function ($index, $label, $name, $checked, $value) {
        return Html::tag('div',
          Html::radio($name, $checked, ['value' => $value, 'id' => $value])
          . Html::tag('label', $label, ['for' => $value]),
          ['class' => 'radio radio-primary radio-inline']);
      },
    ], $options))->label(false);
  }

  /**
   * Просмотр и удаление файла
   * @param string $field атрибут для ActiveField
   * @param string $text текст ссылки
   * @return string
   */
  public function fileManage($field, $text)
  {
    // Генерация ссылок на просмотр и удаление
    $link = $this->wallet->{$field}
      ? Html::a($text, $this->wallet->getFileUrl($field), ['id' => $field . '_show', 'target' => '_blank', 'data-pjax' => 0], true) .
      ' ' . Html::a(Yii::_t('app.common.Delete'), 'javascript: void(0)', [
        'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
        'class' => 'text-danger',
        'id' => $field . '_delete',
      ], true)
      : '-';

    // Враппер
    $html = '<div id="' . $field . '-wrapper" class="form-group">' .
      Html::label(Yii::_t('payments.wallets.attribute-' . $field), null, ['class' => 'control-label']) . ': ' .
      '<span id="' . $field . '-content">' . $link . '</span>' .
      '</div>';
    $html .= $this->form->field($this->wallet, $field, ['inputOptions' => ['id' => $field . '-file']])->hiddenInput()->label(false);

    // Обработка удаления
    Yii::$app->view->registerJs(<<<JS
$("#{$field}_delete").on('click', function(e) {
  var self = $(this);
  
  yii.confirm(self.data('confirm'), function () {
    $('#{$field}-file').val('');
    $('#{$field}-content').text('-');
  });
  
  return false;
  });
JS
    );

    return $html;
  }

  /**
   * @param string $field атрибут для ActiveField
   * @param array $fieldOptions опции ActiveField
   * @return string отреденереный hidden input
   */
  public function hiddenInput($field, $fieldOptions = [])
  {
    return $this->form->field($this->wallet, $field, $fieldOptions)->hiddenInput()->label(false);
  }

  /**
   * Текстовый инпут с маской
   * @param $field
   * @param array $maskOptions
   * @param array $fieldOptions
   * @return \yii\widgets\ActiveField
   */
  public function maskedTextInput($field, $maskOptions = [], $fieldOptions = [])
  {
    if (!isset($maskOptions['options'])) $maskOptions['options'] = [];
    if (!isset($maskOptions['clientOptions'])) $maskOptions['clientOptions'] = [];
    $maskOptions['options']['placeholder'] = ArrayHelper::getValue(
      $maskOptions['options'],
      'placeholder',
      $this->wallet->attributePlaceholder($field)
    );
    $maskOptions['options']['class'] = ArrayHelper::getValue($maskOptions['options'], 'class', 'form-control');
    $maskOptions['clientOptions']['placeholder'] = ArrayHelper::getValue($maskOptions['clientOptions'], 'placeholder', 'x');

    return $this->form->field($this->wallet, $field, $fieldOptions)->widget(MaskedInput::class, $maskOptions);
  }

  /**
   * @param string $field
   * @param array $fieldOptions опции ActiveField
   * @param array $options опции самой textarea
   * @return string отрендеренная textarea
   */
  public function textarea($field, $fieldOptions = [], $options = [])
  {
    return $this->form->field($this->wallet, $field, $fieldOptions)->textarea($options);
  }

  /**
   * Виджет дейтпикера
   * @param string $field атрибут для ActiveField
   * @param array $fieldOptions опции ActiveField
   * @param array $options опции виджета DatePicker
   * @return string отрендеренный виджет
   */
  public function datepicker($field, $fieldOptions = [], $options = [])
  {
    return $this->form->field($this->wallet, $field, $fieldOptions)->widget(DatePicker::class, $options);
  }

  /**
   * Виджет загрузки картинок
   * @param string $field атрибут для ActiveField
   * @param array $options опции
   * @param string $onStartUpload js код который выполнится после начала загрузки
   * @param string $onEndUpload js код который выполнится после окончания загрузки
   * @return string отрендеренный виджет
   */
  public function imageInput($field, $options, $onStartUpload = '', $onEndUpload = '')
  {
    $fieldFile = $this->wallet->formName() . '[' . $field . self::FILE_FIELD_POSTFIX . ']';
    $inputName = $this->wallet->formName() . '[' . $field . ']';

    $readonly = self::isReadOnly($options);

    $initialPreview = [];
    if ($this->wallet->{$field}) {
      $initialPreview = [
        '<img src="' . $this->wallet->getFileUrl($field) . '" style="width: 100%;">',
      ];
    }
    
    $hiddenFieldOptions = [
      'options' => [
        'class' => 'form-group image-hidden'
      ]
    ];

    FileInput::$autoIdPrefix = 'widget_';
    return
      $this->form->field($this->wallet, $field, $hiddenFieldOptions)->hiddenInput() .
      FileInput::widget([
        'options' => [
          'id' => Html::getUniqueId()
        ],
        'name' => $fieldFile,
        'readonly' => $readonly,
        'disabled' => $readonly,
        'pluginOptions' => [
          'allowedFileExtensions' => ['jpg', 'png', 'gif', 'jpeg', 'pdf'],
          'maxFileSize' => 2048,
          'uploadUrl' => Url::to(['/partners/payments/upload-wallet-files/']),
          'uploadExtraData' => [
            'formName' => $this->wallet->formName(),
            'attribute' => $fieldFile,
          ],
          'initialPreviewAsData' => true,
          'initialPreview' => $initialPreview,
          'initialPreviewShowDelete' => true,
          'showClose' => false,
          'showUpload' => false,
          'showRemove' => false,
          'showBrowse' => !$readonly,
          'showUploadedThumbs' => false,
          'initialPreviewConfig' => [
            [
              'width' => '120px',
              // TODO Плагин заставляет указывать URL для удаления файла. Не знаю как это обойти. Нам не нужен запрос на сервер, достаточно очистки инпута
              // TODO Вероятнее всего нужно сделать свою кнопку и самому отлавливать клик
              'url' => Url::to(['/partners/payments/delete-wallet-files/', 'walletId' => $this->userWalletId]),
              'key' => $field,
            ]
          ],
          'previewTemplates' => [
            'image' => '
<div class="file-preview-frame" id="{previewId}" data-fileindex="{fileindex}" data-template="{template}">
  <div class="kv-file-content">
      <img src="{data}" class="kv-preview-data file-preview-image" title="{caption}" alt="{caption}">
  </div>
  {footer}
</div>
',
          ],
          'layoutTemplates' => [
            'btnBrowse' => '<div tabindex="500" class="{css} kartik-file-browse"{status}>{icon}{label}</div>',
            'footer' => '
<div class="file-thumbnail-footer">
  <div class="row"><div class="col-xs-8">{progress}</div><div class="col-xs-4 text-right">{actions}</div></div>
</div>
',
//            'progress' => '
//<div class="progress">
//  <div class="progress-bar progress-bar-success progress-bar-striped text-center" role="progressbar" aria-valuenow="{percent}" aria-valuemin="0" aria-valuemax="100" style="width:{percent}%;">
//      {status}
//   </div>
//</div>',
            'preview' => '
<div class="row">
  <div class="col-xs-6 col-xs-6_50">
    <div class="file-preview {class}">
        <div class="{dropClass}">
          <div class="file-preview-thumbnails">
          </div>
          <div class="clearfix"></div>
          <div class="file-preview-status text-center text-success"></div>
          <div class="kv-fileinput-error"></div>
        </div>
    </div>
  </div>            
  <div class="col-xs-6 col-xs-6_50 text-right">{browse}</div>
</div>
',
            'main1' => '<div class="text-right">{preview}{remove}{cancel}{upload}</div><br>',
            'main2' => '<div class="text-right">{preview}{remove}{cancel}{upload}</div><br>',
            'actionDelete' => '<button type="button" class="kv-file-remove {removeClass}" title="{removeTitle}"{dataUrl}{dataKey}>×</button>',

          ],
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
          'fileuploaderror' => 'function(event, data, msg) { 
              notifyInit(null, msg, false);
              $(\'[name=\"' . $inputName . '\"]\').val(""); 
              $(\'[name="' . $fieldFile . '"]\').fileinput("clear"); // Затирает ошибки плагина
          }',
        ]
      ]);
  }

  /**
   * Вытаскиваем из inputOptions статус readonly,
   * @param $options
   * @return bool
   */
  private static function isReadOnly($options)
  {
    $readonly = false;
    if (isset($options['inputOptions']['readonly'])) {
      $readonly = (bool)$options['inputOptions']['readonly'];
    }
    return $readonly;
  }
}