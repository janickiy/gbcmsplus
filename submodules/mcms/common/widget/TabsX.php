<?php

namespace mcms\common\widget;


use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;

/**
 * Class TabsX
 * Убраны встроенные ассеты
 * @package mcms\common\widget
 */
class TabsX extends \kartik\tabs\TabsX
{
  /**
   * @inheritdoc
   */
  public function initWidget()
  {
    if (empty($this->containerOptions['id'])) {
      $this->containerOptions['id'] = $this->options['id'] . '-container';
    }
    if (ArrayHelper::getValue($this->containerOptions, 'data-enable-cache', true) === false) {
      $this->containerOptions['data-enable-cache'] = "false";
    }
    if ($this->printable) {
      Html::addCssClass($this->options, 'hidden-print');
    }
    Html::addCssClass($this->options, 'tabs-pull-'.($this->align));
    $this->options['role'] = 'tablist';
    $css = self::getCss("tabs-{$this->position}", $this->position != null);
    Html::addCssClass($this->containerOptions, $css);
    Html::addCssClass($this->printHeaderOptions, 'visible-print-block');
  }

  /**
   * Renders tab items as specified in [[items]].
   *
   * @return string the rendering result.
   */
  protected function renderItems()
  {
    $headers = $panes = $labels = [];

    if (!$this->hasActiveTab() && !empty($this->items)) {
      $this->items[0]['active'] = true;
    }

    foreach ($this->items as $n => $item) {
      if (!ArrayHelper::remove($item, 'visible', true)) {
        continue;
      }
      $label = $this->getLabel($item);
      $headerOptions = array_merge($this->headerOptions, ArrayHelper::getValue($item, 'headerOptions', []));
      $linkOptions = array_merge($this->linkOptions, ArrayHelper::getValue($item, 'linkOptions', []));
      $content = ArrayHelper::getValue($item, 'content', '');
      if (isset($item['items'])) {
        foreach ($item['items'] as $subItem) {
          $subLabel = $this->getLabel($subItem);
          $labels[] = $this->printHeaderCrumbs ? $label . $this->printCrumbSeparator . $subLabel : $subLabel;
        }
        $label .= ' <b class="caret"></b>';
        Html::addCssClass($headerOptions, 'dropdown');
        if ($this->renderDropdown($n, $item['items'], $panes)) {
          Html::addCssClass($headerOptions, 'active');
        }
        Html::addCssClass($linkOptions, 'dropdown-toggle');
        $linkOptions['data-toggle'] = 'dropdown';
        $header = Html::a($label, "#", $linkOptions) . "\n"
          . Dropdown::widget([
            'items' => $item['items'],
            'clientOptions' => false,
            'view' => $this->getView()
          ]);
      } else {
        $labels[] = $label;
        $options = array_merge($this->itemOptions, ArrayHelper::getValue($item, 'options', []));
        $options['id'] = ArrayHelper::getValue($options, 'id', $this->options['id'] . '-tab' . $n);
        $css = 'tab-pane';
        $isActive = ArrayHelper::remove($item, 'active');
        if ($this->fade) {
          $css = $isActive ? "{$css} fade in" : "{$css} fade";
        }
        Html::addCssClass($options, $css);
        if ($isActive) {
          Html::addCssClass($options, 'active');
          Html::addCssClass($headerOptions, 'active');
        }
        if (isset($item['url'])) {
          $header = Html::a($label, $item['url'], $linkOptions);
        } else {
          $linkOptions['data-toggle'] = 'tab';
          $linkOptions['role'] = 'tab';
          $header = Html::a($label, '#' . $options['id'], $linkOptions);
        }
        if ($this->renderTabContent) {
          $panes[] = Html::tag('div', $content, $options);
        }
      }
      $headers[] = Html::tag('li', $header, $headerOptions);
    }
    $outHeader = Html::tag('ul', implode("\n", $headers), $this->options);
    if ($this->renderTabContent) {
      /** Добавлен класс padding-10 */
      $outPane = Html::beginTag('div', ['class' => 'tab-content padding-10' . $this->getCss('printable', $this->printable)]);
      foreach ($panes as $i => $pane) {
        if ($this->printable) {
          $outPane .= Html::tag('div', ArrayHelper::getValue($labels, $i), $this->printHeaderOptions) . "\n";
        }
        $outPane .= "$pane\n";
      }
      $outPane .= Html::endTag('div');
      $tabs = $this->position == self::POS_BELOW ? $outPane . "\n" . $outHeader : $outHeader . "\n" . $outPane;
    } else {
      $tabs = $outHeader;
    }
    return Html::tag('div', $tabs, $this->containerOptions);
  }
}