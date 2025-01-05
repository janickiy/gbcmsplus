<?php


namespace mcms\common\traits;

use Yii;

/**
 * Class Translate
 * Если у модели или контроллера определить const LANG_PREFIX = 'promo.landing_unblock_requests.';
 * То можно пользователься методом Model::translate(), куда передавать последнюю часть строки для перевода.
 *
 * То есть вместо Yii::_t('promo.landing_unblock_requests.list');
 * Можно написать Model::translate('list');
 *
 * @package mcms\common\traits
 */
trait Translate
{
  /**
   * @param $label
   * @param array $params
   * @return string
   */
  static public function translate($label, $params = [])
  {
    return Yii::_t(self::LANG_PREFIX . $label, $params);
  }

  /**
   * @see translate()
   * @param $label
   * @param array $params
   * @return string
   */
  static public function t($label, $params = [])
  {
    return static::translate($label, $params);
  }

  /**
   * Передаем в функцию массив аттрибутов, получаем массив аттрибутов с их
   * переведенными лейблами. Подразумевается что все лейблы хранятся под названиями
   * "attribute-{attribute_name}", например "attribute-created_by"
   *
   * @param $attributes
   * @return array
   */
  public function translateAttributeLabels($attributes)
  {
    $translated = [];
    foreach ($attributes as $attribute) {
      $translated[$attribute] = self::translate('attribute-' . $attribute);
    }
    return $translated;
  }
}