<?php

namespace mcms\notifications\components\widgets\notifyHeader\smart;

use mcms\notifications\components\widgets\notifyHeader\AbstractNotifyHeaderWidget;

class NotifyHeaderWidget extends AbstractNotifyHeaderWidget
{
  function registerAsset()
  {
    NotifyHeaderAsset::register($this->getView());
  }
}
