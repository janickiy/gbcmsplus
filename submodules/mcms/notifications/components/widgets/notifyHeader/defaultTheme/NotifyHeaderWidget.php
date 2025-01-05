<?php

namespace mcms\notifications\components\widgets\notifyHeader\defaultTheme;

use mcms\notifications\components\widgets\notifyHeader\AbstractNotifyHeaderWidget;

class NotifyHeaderWidget extends AbstractNotifyHeaderWidget
{
  function registerAsset()
  {
    NotifyHeaderAsset::register($this->getView());
  }
}
