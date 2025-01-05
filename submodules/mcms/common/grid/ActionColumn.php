<?php
namespace mcms\common\grid;

use Closure;
use mcms\common\helpers\ArrayHelper;
use rgk\utils\widgets\AjaxButton as RGKAjaxButtons;
use mcms\common\widget\modal\Modal;
use mcms\notifications\Module;
use rgk\utils\widgets\AjaxButton;
use yii\bootstrap\Html;
use Yii;
use yii\grid\ActionColumn as GridActionColumn;
use yii\helpers\BaseInflector;
use yii\helpers\Url;

/**
 * TRICKY Если после нажатия на кнопку удалить или ей подобную, страница перезагружается без ajax, значит таблица не обернута в pjax-контейнер
 * @see mcms/common/grid/assets/js/ajax-buttons.js
 */
class ActionColumn extends GridActionColumn
{

  public $buttonsPath = [];

  public function init()
  {
    if (!$this->controller) {
      $currentUrl = Yii::$app->urlManager->parseRequest(Yii::$app->request)[0];
      $this->controller = substr($currentUrl, 0, strrpos($currentUrl, '/'));
    }

    $this->setTemplate($this->template);

    if (!isset($this->buttons['view'])) {
      $this->buttons['view'] = function ($url, $model, $key) {
        if (!$this->isVisible('view', $model)) return null;

        $options = array_merge([
          'title' => Yii::t('yii', 'View'),
          'aria-label' => Yii::t('yii', 'View'),
          'data-pjax' => '0',
          'class' => 'btn btn-xs btn-default'
        ], $this->buttonOptions);
        return Html::a(Html::icon('eye-open'), $url, $options);
      };
    }

    if (!isset($this->buttons['view-modal'])) {
      $this->buttons['view-modal'] = function ($url, $model) {
        return Modal::widget([
          'toggleButtonOptions' => array_merge([
            'tag' => 'a',
            'label' => Html::icon('eye-open'),
            'title' => Yii::t('yii', 'View'),
            'class' => 'btn btn-xs btn-default',
            'data-pjax' => 0,
          ], $this->buttonOptions),
          'url' => $url,
        ]);
      };
    }

    if (!isset($this->buttons['delete'])) {
      $this->buttons['delete'] = function ($url, $model, $key) {
        $options = array_merge([
          'title' => Yii::t('yii', 'Delete'),
          'aria-label' => Yii::t('yii', 'Delete'),
          RGKAjaxButtons::CONFIRM_ATTRIBUTE => Yii::t('yii', 'Are you sure you want to delete this item?'),
          'data-pjax' => 0,
          RGKAjaxButtons::RELOAD_ATTRIBUTE => 1,
          'class' => 'btn btn-xs btn-default'
        ], $this->buttonOptions);
        return AjaxButton::widget(['options' => $options, 'text' => Html::icon('trash'), 'url' => $url]);
      };
    }

    if (!isset($this->buttons['update'])) {
      $this->buttons['update'] = function ($url, $model) {
        if (!$this->isVisible('update', $model)) return null;
        $options = array_merge([
          'title' => Yii::t('yii', 'Update'),
          'aria-label' => Yii::t('yii', 'Update'),
          'data-pjax' => '0',
          'class' => 'btn btn-xs btn-default'
        ], $this->buttonOptions);
        return Html::a(Html::icon('pencil'), $url, $options);
      };
    }

    if (!isset($this->buttons['update-modal'])) {
      $this->buttons['update-modal'] = function ($url, $model) {
        return Modal::widget([
          'toggleButtonOptions' => array_merge([
            'tag' => 'a',
            'label' => Html::icon('pencil'),
            'title' => Yii::t('yii', 'Update'),
            'class' => 'btn btn-xs btn-default',
            'data-pjax' => 0,
          ], $this->buttonOptions),
          'url' => $url,
        ]);
      };
    }

    if (!isset($this->buttons['disable'])) {
      $this->buttons['disable'] = function ($url, $model) {
        if (!method_exists($model, 'isDisabled')) return null;
        if ($model->isDisabled()) return null;
        $options = array_merge([
          'title' => Yii::t('yii', 'Off'),
          'aria-label' => Yii::t('yii', 'Off'),
          'data-pjax' => 0,
          'class' => 'btn btn-xs btn-warning',
          RGKAjaxButtons::RELOAD_ATTRIBUTE => 1
        ], $this->buttonOptions);

        return AjaxButton::widget(['options' => $options, 'text' => Html::icon('remove'), 'url' => $url]);
      };
    }

    if (!isset($this->buttons['enable'])) {
      $this->buttons['enable'] = function ($url, $model) {
        if (!method_exists($model, 'isDisabled')) return null;
        if (!$model->isDisabled()) return null;
        $options = array_merge([
          'title' => Yii::t('yii', 'On'),
          'aria-label' => Yii::t('yii', 'On'),
          'data-pjax' => 0,
          'class' => 'btn btn-xs btn-success',
          RGKAjaxButtons::RELOAD_ATTRIBUTE => 1
        ], $this->buttonOptions);

        return AjaxButton::widget(['options' => $options, 'text' => Html::icon('ok'), 'url' => $url]);
      };
    }

    if (!isset($this->buttons['enable-modal'])) {
      $this->buttons['enable-modal'] = function ($url, $model) {
        if (!method_exists($model, 'isDisabled')) return null;
        if (!$model->isDisabled()) return null;
        return Modal::widget([
          'toggleButtonOptions' => array_merge([
            'tag' => 'a',
            'label' => Html::icon('ok'),
            'title' => Yii::t('yii', 'On'),
            'class' => 'btn btn-xs btn-success',
            'data-pjax' => 0,
          ], $this->buttonOptions),
          'url' => $url,
        ]);
      };
    }

    parent::init();

    $this->header = isset($this->header) ? $this->header : Yii::_t("commonMsg.main.actions");
  }

  /**
   * Проверка видимости кнопки
   * @param $button
   * @param $model
   * @return bool
   */
  private function isVisible($button, $model)
  {
    if (isset($this->visibleButtons[$button])) {
      $isVisible = $this->visibleButtons[$button] instanceof \Closure
        ? call_user_func($this->visibleButtons[$button], $model)
        : $this->visibleButtons[$button];
    } else {
      $isVisible = true;
    }
    return $isVisible;
  }

  public function setTemplate($template)
  {
    $this->template = sprintf('<div class="btn-group">%s</div>', $template);
  }

  /**
   * Creates a URL for the given action and model.
   * This method is called for each button and each row.
   * @param string $action the button name (or action ID)
   * @param \yii\db\ActiveRecord $model the data model
   * @param mixed $key the key associated with the data model
   * @param integer $index the current row index
   * @return string the created URL
   */
  public function createUrl($action, $model, $key, $index)
  {
    if (!($path = ArrayHelper::getValue($this->buttonsPath, $action))) {
      $path = '/' . ($this->controller ? $this->controller . '/' . $action : $action);
    }
    $params = is_array($key) ? $key : ['id' => (string)$key];
    if ($fn = Yii::$app->request->getQueryParam(Module::FN_QUERY_PARAM)) {
      $params[Module::FN_QUERY_PARAM] = $fn;
    }

    if (is_array($path)) {
      return Yii::$app->user->can(BaseInflector::camelize($path[0])) ? Url::to(array_merge($path, $params)) : false;
    }

    if ($this->urlCreator instanceof Closure) {
      return Yii::$app->user->can(BaseInflector::camelize($path))
        ? call_user_func($this->urlCreator, $action, $model, $key, $index)
        : false
      ;
    }

    return Yii::$app->user->can(BaseInflector::camelize($path))
      ? Url::toRoute(array_merge([$path], $params))
      : false
    ;
  }

  /**
   * @inheritdoc
   */
  protected function renderDataCellContent($model, $key, $index)
  {
    return preg_replace_callback('/\\{([\w\-\/]+)\\}/', function ($matches) use ($model, $key, $index) {
      $name = $matches[1];

      if (isset($this->visibleButtons[$name])) {
        $isVisible = $this->visibleButtons[$name] instanceof \Closure
          ? call_user_func($this->visibleButtons[$name], $model, $key, $index)
          : $this->visibleButtons[$name];
      } else {
        $isVisible = true;
      }

      if (!isset($this->buttons[$name]) || !$isVisible) return '';

      $url = $this->createUrl($name, $model, $key, $index);

      return $url ? call_user_func($this->buttons[$name], $url, $model, $key) : '';

    }, $this->template);
  }
}