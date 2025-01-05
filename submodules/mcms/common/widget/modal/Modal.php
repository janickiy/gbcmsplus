<?php

namespace mcms\common\widget\modal;


use kartik\form\ActiveForm;
use mcms\common\actions\GetModalAction;
use mcms\common\traits\WidgetUniqueIdTrait;
use Yii;
use yii\base\InvalidParamException;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use mcms\common\helpers\Html as OurHtml;

class Modal extends Widget
{
  use WidgetUniqueIdTrait;

  const SIZE_LG = 'modal-lg';
  const SIZE_MD = '';
  const SIZE_SM = 'modal-sm';

  const WRAPPER_ID = '#modalWidget';

  public $title = '';

  public $toggleButton = true;

  /**
   * Путь до View
   * @var string
   */
  public $viewPath;

  /**
   * Параметры, передающиеся в вид
   * @var array
   */
  public $viewParams = [];

  /**
   * TRICKY: Если url передается в виде массива, пройдет проверка прав на ссылку. Иначе проверка игнорируется
   * @var string|array
   */
  public $url;

  /**
   * @var string HTTP метод запроса модалки (почему POST? потому что было так)
   */
  public $requestMethod = 'post';

  /**
   * Опции кнопки открытия модалки
   * По умолчанию [
   *    'tag' => 'button',
   *    'type' => 'button',
   *    'label' => $this->title,
   *    'href' => '#'
   * ]
   * @var array
   */
  public $toggleButtonOptions = [];

  /**
   * @var array
   */
  public $options = [];

  /**
   * @var string
   */
  public $size = '';

  /**
   * true - открывать в новую модалку
   * false - затирать предыдущую
   * @var bool
   */
  public $single = false;

  private static $_renderedViews = [];
  private static $_renderedModals = [];

  public function init()
  {
    parent::init();

    if (!$this->url && !$this->viewPath) {
      throw new InvalidParamException(\Yii::_t('commonMsg.main.required_view_path'));
    }

    if (!isset($this->options['id'])) {
      $this->options['id'] = $this->getId();
    }

    if (!self::$_renderedModals) {
      // Первый запуск виджета на странице
      $this->view->blocks['modals'] = '';
      $this->url && $this->view->registerJs("ModalWidget.init();");
    }
    $this->url && $this->toggleButtonOptions = ArrayHelper::merge($this->toggleButtonOptions, [
      'data-url' => is_array($this->url) ? Url::to($this->url) : $this->url,
    ]);

    $this->toggleButtonOptions['data-modal-method'] = $this->requestMethod;

    self::$_renderedModals[] = $this->options['id'];

    $this->initOptions();
  }

  public function run()
  {
    if (is_array($this->url) && !OurHtml::hasUrlAccess($this->url)) {
      return false;
    }
    $this->url && ModalAsset::register($this->view);
    if(!$this->url && $this->viewPath) {
      $this->view->blocks['modals'] .= $this->renderModalContainer($this->viewPath, $this->viewParams);
    }
    return $this->toggleButton ? $this->renderToggleButton() : null;
  }

  /**
   * Рендерит кнопку открытия модалки
   * @return string
   */
  protected function renderToggleButton()
  {
    $options = $this->toggleButtonOptions;
    $tag = ArrayHelper::remove($options, 'tag', 'button');
    $label = ArrayHelper::remove($options, 'label', $this->title);
    if ($tag === 'a' && !isset($options['href'])) {
      $options['href'] = '#';
    }
    if ($tag === 'button' && !isset($options['type'])) {
      $options['type'] = 'button';
    }

    return Html::tag($tag, $label, $options);
  }

  /**
   * Рендерит обертку модалки
   * @param string $viewPath
   * @param array $viewParams
   * @return string
   */
  protected function renderModalContainer($viewPath, $viewParams = [])
  {
    $html = Html::beginTag('div', $this->options) . "\n";
    $html .= Html::beginTag('div', ['class' => 'modal-dialog '.$this->size]) . "\n";
    $html .= Html::beginTag('div', ['class' => 'modal-content']) . "\n";

    $html .= $this->render($viewPath, $viewParams);

    $html .= "\n" . Html::endTag('div'); // modal-content
    $html .= "\n" . Html::endTag('div'); // modal-dialog
    $html .= "\n" . Html::endTag('div');

    return $html;
  }

  /**
   * Инициализирует параметры модалки (взято из yii\bootstrap\Modal)
   */
  protected function initOptions()
  {
    $this->options = array_merge([
      'class' => 'fade',
      'role' => 'dialog',
//      'tabindex' => -1, из-за этого параметра ломаются select2 в модалках
    ], $this->options);
    Html::addCssClass($this->options, ['widget' => 'modal']);

    $this->toggleButtonOptions = array_merge([
      'data-toggle' => 'modal',
      'data-size' => $this->size,
    ], $this->toggleButtonOptions);
    if (!isset($this->toggleButtonOptions['data-target']) && !isset($this->toggleButtonOptions['href'])) {
      $this->toggleButtonOptions['data-target'] = !$this->single ? static::WRAPPER_ID : '#' . $this->options['id'];
    }
  }

  public static function ajaxSuccess($pjaxId = null, $jsExpression = '')
  {
    $emptyModal = new jsExpression("ModalWidget && ModalWidget.empty();");
    $pjaxReload = $pjaxId ? new jsExpression("$.pjax.reload({container : '$pjaxId', 'timeout' : 5000});") : '';
    return new jsExpression("function(response){
      $emptyModal
      $pjaxReload
      $jsExpression
    }");
  }
}