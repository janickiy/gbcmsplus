<?php
namespace mcms\common\widget;

use mcms\common\web\AjaxResponse;
use mcms\common\widget\alert\AlertAsset;
use yii\base\Widget;
use Yii;

/**
 * @deprecated @see AjaxButtonsAsset в utils (TRICKY Новая версия полностью переработана)
 * TRICKY Если после нажатия на кнопку удалить или ей подобную, страница перезагружается без ajax, значит кнопка не обернута в pjax-контейнер
 *
 * Пример использования
 *
 * 1) Во вьюхе вызываем виджет AjaxButtons::widget(); (по-умолчанию подключено в лейауте)
 * 2) Рисуем кнопку/ссылку:
   <?= Html::a('удал.', ['delete-field', 'id' => $field->id],
    [
      'class' => 'text-danger',
      'data-pjax' => 0,
      AjaxButtons::CONFIRM_ATTRIBUTE => Yii::t('yii', 'Are you sure you want to delete this item?'),
      AjaxButtons::AJAX_ATTRIBUTE => 1
    ])?>
 *
 * @see mcms/common/grid/assets/js/ajax-buttons.js
 */
class AjaxButtons extends Widget
{
  const AJAX_ATTRIBUTE = 'data-ajaxable';
  const CONFIRM_ATTRIBUTE = 'data-confirm-text';
  const SUCCESS_ATTRIBUTE = 'data-ajaxable-success';
  const RELOAD_ATTRIBUTE = 'data-ajaxable-reload';
  const RELOAD_CONTAINER_ATTRIBUTE = 'data-ajaxable-reload-container';
  const RELOAD_URL_ATTRIBUTE = 'data-ajaxable-reload-url';

  const AJAX_SUCCESS_TEXT = '{{successText}}';
  const AJAX_FAIL_TEXT = '{{failText}}';

  const SUCCESS_REPLACE = '{{successParam}}';
  const ERROR_REPLACE = '{{errorParam}}';

  public $buttonsPath = [];


  public function init()
  {
    parent::init();

    AlertAsset::register($this->view);

    $jsCodeAjaxBtns = file_get_contents(
      __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .
      'assets' . DIRECTORY_SEPARATOR .
      'resources' . DIRECTORY_SEPARATOR .
      'js' . DIRECTORY_SEPARATOR .
      'ajax-buttons.js'
    );
    $jsCodeAjaxBtns = str_replace(self::AJAX_SUCCESS_TEXT, Yii::_t('app.common.operation_success'), $jsCodeAjaxBtns);
    $jsCodeAjaxBtns = str_replace(self::AJAX_FAIL_TEXT, Yii::_t('app.common.operation_failure'), $jsCodeAjaxBtns);
    $jsCodeAjaxBtns = str_replace(self::SUCCESS_REPLACE, AjaxResponse::DEFAULT_SUCCESS_PARAM, $jsCodeAjaxBtns);
    $jsCodeAjaxBtns = str_replace(self::ERROR_REPLACE, AjaxResponse::DEFAULT_ERROR_PARAM, $jsCodeAjaxBtns);
    $this->view->registerJs($jsCodeAjaxBtns);
  }
}