<?php

namespace mcms\common\web;

/**
 * Переопределенный класс /vendor/yiisoft/yii2/Request.php для возможности расширения
 */
class Request extends \yii\web\Request
{

  /**
   * Получение реального ip пользователя
   * @return string|null user IP address, null if not available
   */
  public function getUserIP()
  {
    return parent::getUserIP();

//    сейчас CF отдает в заголовек REMOTE_ADDR нормальный айпишник. Поэтому заглушили костыль ниже.
//    return isset($_SERVER['HTTP_X_FORWARDED_FOR'])
//      ? $_SERVER['HTTP_X_FORWARDED_FOR']
//      : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);
  }

}
