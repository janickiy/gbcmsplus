<?php
namespace mcms\promo\models;

use yii\base\Model;

/**
 * Базовый класс для настроек провайдера
 */
abstract class AbstractProviderSettings extends Model
{
  /**
   * Название вьюхи для формы
   * @return string
   */
  abstract public function getViewName();
}