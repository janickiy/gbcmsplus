<?php

namespace mcms\common\web;


use mcms\common\helpers\Html;
use yii\base\Component;


/**
 * Данный компонент подключается в конфиге приложения
 */
class UrlAccess extends Component
{


  /**
   * Проверка на доступ по урлу.
   * Пример: \Yii::$app->urlAccess->can(['/admin/permission/index'])
   * @param $url
   * @param array $params
   * @return bool
   */
  public function can($url, $params = [])
  {
    return Html::hasUrlAccess($url);
  }

}