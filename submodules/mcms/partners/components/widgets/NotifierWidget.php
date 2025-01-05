<?php

namespace mcms\partners\components\widgets;

use mcms\partners\assets\NotifierAsset;
use Yii;
use yii\base\Widget;
use yii\web\View;

/**
 * Использование:
 *
 * notifyInit('Спасибо!', 'Кошелек успешно изменен.', true);
 *
 * или без заголовка:
 *
 * notifyInit(null, 'Кошелек успешно изменен.', true);
 *
 * Class NotifierWidget
 * @package mcms\partners\components\widgets
 */
class NotifierWidget extends Widget
{

  public function run()
  {
    NotifierAsset::register($this->view);

    $this->view->registerJs(/** @lang JavaScript */ 'var notifyInit = function (title, message, success) {
  var letters = 0;
  if (title) letters += title.length;
  if (message) letters += message.length;
  var timeout = letters * 50;
  if (timeout < 3000) timeout = 3000;

  var icon = success ? "icon-checked_big" : "icon-cancel_4";

  return $.notify({
    icon: icon,
    title: title,
    message: message,
  }, {
    type: success ? "success" : "danger",
    placement: {
      from: "top",
      align: "right"
    },
    offset: 62,
    delay: timeout,
    timer: 1000,
    z_index: 1100,
    animate: {
      enter: "animated slideInDown",
      exit: "animated slideOutUp"
    },
  });
}', View::POS_END, 'Notifier');
  }
}