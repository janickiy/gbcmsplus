<?php

namespace mcms\common\grid;

use yii\base\Widget;
use yii\bootstrap\Html;
use mcms\common\helpers\Html as CustomHtml;
use yii\helpers\Json;

class ContentViewPanel extends Widget
{
    const BUTTON_FULLSCREEN = 'fullscreen';
    const BUTTON_TOGGLE = 'toggle';
    const BUTTON_DELETE = 'delete';
    public $labelDelete = 'Delete widget:';
    public $deleteMsg = 'Warning: This action cannot be undone!';
    public $onDelete = 'function() {}';
    public $header;
    /** @var string Отличается от header отсутствием окружения <h2> */
    public $label;
    public $padding = true;
    public $options = [];
    public $jsOptions = [];
    public $renderWrapper = true;
    public $toolbar = false;
    public $buttons = [
        self::BUTTON_FULLSCREEN,
    ];
    private $_widgetId;

    public function init()
    {
        parent::init();
        $this->registerJsScripts();
        echo $this->beginWidget();
    }

    public function run()
    {
        echo $this->endWidget();
    }

    /**
     * Уникальный id для виджета
     * @return string
     */
    private function getWidgetId()
    {
        return $this->_widgetId ?: $this->_widgetId = CustomHtml::getUniqueId();
    }

    protected function beginWidget()
    {
        $paddingClass = $this->padding ? '' : 'no-padding';
        $containerClass = array_key_exists('class', $this->options) ? $this->options['class'] : '';
        $html = $this->renderWrapper ? '<section id="' . $this->getWidgetId() . '">' : '';
        $toolbar = $this->toolbar ? '<div class="widget-toolbar" role="menu">
                                      ' . $this->toolbar . '
                                  </div>' : null;
        $html .= <<<HTML
  <div role="widget" class="jarviswidget jarviswidget-color-white $containerClass" id="widget-{$this->getWidgetId()}">
    <header role="heading">
    <div class="jarviswidget-ctrls" role="menu"> 
      {$this->getButtons()}
    </div>
      $this->label
      <h2>$this->header</h2>
      $toolbar
      <span class="jarviswidget-loader"><i class="fa fa-refresh fa-spin"></i></span>
    </header>
    <div role="content">
      <div class="widget-body $paddingClass">
HTML;
        return $html;
    }

    protected function endWidget()
    {
        $html = <<<HTML
      </div>
    </div>
  </div>
HTML;
        $html .= $this->renderWrapper ? '</section>' : '';
        return $html;
    }

    public function registerJsScripts()
    {
        $jsOptions = Json::encode($this->jsOptions);
        $js = <<<JS
    var options = {
			"grid": "article",
			"widgets": ".jarviswidget",
			"localStorage": localStorageJarvisWidgets,
			"deleteSettingsKey": "#deletesettingskey-options",
			"settingsKeyLabel": "Reset settings?",
			"deletePositionKey": "#deletepositionkey-options",
			"positionKeyLabel": "Reset position?",
			"sortable": false,
			"buttonsHidden": false,
			"toggleButton": true,
			"toggleClass": "fa fa-minus | fa fa-plus",
			"toggleSpeed": 200,
			"onToggle": function() {},
			"deleteButton": true,
			"deleteMsg": "{$this->deleteMsg}",
			"deleteClass": "fa fa-times",
			"deleteSpeed": 200,
			"onDelete": {$this->onDelete},
			"editButton": true,
			"editPlaceholder": ".jarviswidget-editbox",
			"editClass": "fa fa-cog | fa fa-save",
			"editSpeed": 200,
			"onEdit": function() {},
			"colorButton": true,
			"fullscreenButton": true,
			"fullscreenClass": "fa fa-expand | fa fa-compress",
			"fullscreenDiff": 3,
			"onFullscreen": function() {},
			"customButton": false,
			"customClass": "folder-10 | next-10",
			"customStart": function() {
				alert("Hello you, this is a custom button...")
			},
			"customEnd": function() {
				alert("bye, till next time...")
			},
			"buttonOrder": "%refresh% %custom% %edit% %toggle% %fullscreen% %delete%",
			"opacity": 1,
			"dragHandle": "> header",
			"placeholderClass": "jarviswidget-placeholder",
			"indicator": true,
			"indicatorTime": 600,
			"ajax": true,
			"timestampPlaceholder": ".jarviswidget-timestamp",
			"timestampFormat": "Last update: %m%/%d%/%y% %h%:%i%:%s%",
			"refreshButton": true,
			"refreshButtonClass": "fa fa-refresh",
			"labelError": "Sorry but there was a error:",
			"labelUpdated": "Last Update:",
			"labelRefresh": "Refresh",
			"labelDelete": "{$this->labelDelete}",
			"afterLoad": function() {},
			"rtl": false,
			"onChange": function() {},
			"onSave": function() {},
			"ajaxnav": $.navAsAjax
		};
		options = $.extend(options, $jsOptions);
    $("#{$this->getWidgetId()}").jarvisWidgets(options);
JS;
        $this->view->registerJs($js);
    }

    /**
     * @return array|null|string
     */
    protected function getButtons()
    {
        if (!$this->buttons) {
            return null;
        }
        if (!is_array($this->buttons)) {
            return $this->buttons;
        }
        $buttons = '';
        foreach ($this->buttons as $type) {
            $buttons .= static::getButton($type);
        }
        return $buttons;
    }

    /**
     * Получение кнопки по типу
     * @param $type
     * @return null|string
     */
    protected function getButton($type)
    {
        switch ($type) {
            case self::BUTTON_FULLSCREEN:
                return $this->renderButton('expand', self::BUTTON_FULLSCREEN);
            case self::BUTTON_TOGGLE:
                return $this->renderButton('minus', self::BUTTON_TOGGLE);
            case self::BUTTON_DELETE:
                return $this->renderButton('times', self::BUTTON_DELETE);
        }
        return null;
    }

    /**
     * Рендер кнопки
     * @param $icon
     * @param $type
     * @return string
     */
    private function renderButton($icon, $type)
    {
        return Html::a(
            Html::icon($icon, ['prefix' => 'fa fa-']),
            'javascript:void(0);',
            [
                'class' => 'button-icon jarviswidget-' . $type . '-btn',
                'rel' => 'tooltip',
                'data-placement' => 'bottom'
            ]
        );
    }
}
