<?php

namespace mcms\common\widget;


use mcms\common\helpers\Html;
use mcms\common\widget\alert\Alert;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * Class AjaxRequest
 * @package mcms\common\widget
 */
class AjaxRequest extends Widget
{
  const BUTTON_CLASS = 'ajax-request';

  /**
   * класс кнопки
   * @var string
   */
  public $buttonClass;

  /**
   * Текст в случае успеха
   * @var string
   */
  public $onSuccess = '';

  /**
   * js перед отправкой данных
   * @var string
   */
  public $beforeSubmit;

  /**
   * Текст в случае ошибки
   * @var string
   */
  public $onError = '';

  /**
   * @var string|array
   */
  public $url;

  /**
   * Текст кнопки
   * @var string
   */
  public $title = '';

  /**
   * Текст подтверждения
   * @var string
   */
  public $confirm = '';

  /**
   * Метод отправки
   * @var string
   */
  public $method = 'POST';

  /**
   * id pjax контейнера, который надо перезагрузить
   * @var string
   */
  public $pjaxId = '';

  /**
   * Данные, которые будут переданы при запросе
   * @var string
   */
  public $value = '';

  /**
   * Параметры кнопки
   * @var array
   */
  public $options = [];

  /**
   * Использовать контроль доступа, для отображения ссылки
   * @var bool
   */
  public $useAccessControl = true;
  public $returnEmptyString = false;

  /**
   * @inheritdoc
   */
  public function init()
  {
    parent::init();

    !$this->buttonClass && $this->buttonClass = self::BUTTON_CLASS;

    if (!$this->url) {
      throw new InvalidConfigException(Yii::t('yii', 'Missing required parameters: {params}', ['params' => 'url']));
    }

    !$this->onSuccess && $this->onSuccess = Yii::_t('partners.main.operation_success');
    !$this->onError && $this->onError = Yii::_t('partners.main.operation_fail');

    $this->view->registerJs($this->js);
  }

  /**
   * @return string
   */
  public function run()
  {
    return $this->renderButton();
  }

  /**
   * @return string
   */
  protected function renderButton()
  {
    $options = $this->options;
    $options['href'] = Url::to($this->url);
    Html::addCssClass($options, $this->buttonClass);
    $this->value !== null && $options['data-value'] = $this->value;
    $this->confirm && $options['data-confirm-text'] = $this->confirm;

    if ($this->useAccessControl && !Html::hasUrlAccess($this->url)) {
      return $this->returnEmptyString ? $this->title : null;
    }

    return Html::tag('a', $this->title, $options);
  }

  /**
   * @return string
   */
  protected function getJs()
  {
    $success = Alert::success($this->onSuccess);
    $error = Alert::warning($this->onError);
    $class = $this->buttonClass;

    $pjaxReload = $this->pjaxId ? new jsExpression("$.pjax.reload({container : '{$this->pjaxId}', 'timeout' : 5000});") : '';


    $js = <<<JS
$('.{$class}').click(function(e){
  e.preventDefault();
  {$this->beforeSubmit}
  
  var url = $(this).attr('href'),
    value = $(this).data('value'),
    confirmText = $(this).data('confirm-text');
  
  var makeRequest = function () {
    $.ajax({
      type: '{$this->method}',
      url: url,
      data: {
        value: value
      },
      dataType: 'json',
      success: function(res) {
        if (res.success === true) {
            $pjaxReload
            $success
          } else {
            $error
          }
        }
    });
  };
  
  if (confirmText) {
    yii.confirm(confirmText, makeRequest);
    return;
  }
  
  makeRequest();
});
JS;

    return $js;
  }
}