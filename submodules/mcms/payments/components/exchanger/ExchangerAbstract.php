<?php

namespace mcms\payments\components\exchanger;

use Yii;
use yii\base\Object;
use yii\console\Application;

abstract class ExchangerAbstract extends Object implements ExchangerInterface
{
  protected $defaultPercent = 6;
  protected $minimumPercent = 1;

  protected static function consoleLog($message, array $params = [])
  {
    if (!Yii::$app instanceof Application) return null;

    $args = func_get_args();
    array_shift($args);
    array_shift($args);

    $message = $params
      ? strtr($message, array_map(function ($elem) {
        return is_array($elem) ? implode(', ', $elem) : $elem;
      }, $params))
      : $message;

    return call_user_func_array([Yii::$app->controller, 'stdout'], array_merge([$message], $args));
  }
}