<?php

namespace mcms\notifications\components\api;

use mcms\common\module\api\ApiResult;
use Yii;
use yii\helpers\ArrayHelper;

class NotifyHeaderWidget extends ApiResult
{
  function init($params = [])
  {
    $theme = ArrayHelper::getValue($params, 'theme', 'default');
    $widgetClass = null;
    unset($params['theme']);
    switch ($theme) {
      case 'smart':
        $widgetClass = \mcms\notifications\components\widgets\notifyHeader\smart\NotifyHeaderWidget::class;
        break;
      case 'basic':
        $widgetClass = \mcms\notifications\components\widgets\notifyHeader\basic\NotifyHeaderWidget::class;
        break;
      case 'default':
      default:
        $widgetClass = \mcms\notifications\components\widgets\notifyHeader\defaultTheme\NotifyHeaderWidget::class;
        break;
    }

    $this->prepareWidget($widgetClass, $params);
  }
}