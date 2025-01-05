<?php

namespace mcms\common\widget;

use mcms\common\traits\WidgetUniqueIdTrait;

class Editable extends \kartik\editable\Editable
{
  use WidgetUniqueIdTrait;

  public static function getWidget(array $config, array $url, $asPopover = false, $reloadModalIfSuccess = true)
  {
    $config['asPopover'] = $asPopover;
    if ($reloadModalIfSuccess) {
      $config['pluginEvents']['editableSuccess'] = "function() { ModalWidget.reload() }";
    }

    $config['formOptions'] = [
      'action' => $url,
    ];

    return parent::widget($config);
  }
}