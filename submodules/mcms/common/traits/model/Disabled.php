<?php

namespace mcms\common\traits\model;

use Yii;

/**
 * Деактивация
 *
 * Возможности
 * -----------
 * - добавляет методы для управления активностью элемента
 *
 * Инструкция
 * ----------
 * - подключить трейт к модели
 * - добавить в таблицу поле is_disabled
 * - добавить в модель правило
 * ```
 * [['is_disabled'], 'boolean'],
 * ```
 *
 * Инструкция по использованию поля is_enabled
 * -------------------------------------------
 * Так как часто в интерфейсе вместо чекбокса "Деактивировано" используется лейбл "Активность" для поля is_disabled
 * добавлен синоним is_enabled.
 * Для его использования достаточно добавить в модель правило
 * ```
 * [['is_enabled'], 'boolean'],
 * ```
 * В БД хранится только поле is_disabled. is_enabled всего лишь виртуальное свойство использующее инверсию is_disabled
 *
 *
 * Пример использования
 * --------------------
 * ```
 * class News {
 *    use Disabled;
 *
 *    public function rules() {
 *        return [
 *          [['is_enabled', 'is_disabled'], 'boolean'],
 *        ];
 *    }
 * }
 *
 * $game = new News;
 * $game->setEnabled(); // активировать новость
 * $game->setDisabled(); // деактивировать новость
 * $game->isDisabled(); // новость не активна?
 *
 * $form->field('is_enabled')->checkbox(); // Поле "Активировать"
 * $form->field('is_disabled')->checkbox(); // Поле "Деактивировать"
 * ```
 *
 * Пример миграции
 * ---------------
 * ```
 * public function up()
 * {
 *    $this->addColumn('karapuzi', 'is_disabled', $this->boolean()->defaultValue(false)->after('id'));
 * }
 *
 * public function down()
 * {
 *    $this->dropColumn('karapuzi', 'is_disabled');
 * }
 * ```
 *
 * Пример лейблов
 * --------------
 * ```
 * public function attributeLabels()
 * {
 *    return [
 *        'is_enabled' => $this->getIsDisabledLabel(),
 *        'is_disabled' => $this->getIsDisabledLabel(),
 *    ];
 * }
 * ```
 *
 * Пример отображения в списке
 * ---------------------------
 * ```
 * [
 *    'attribute' => 'is_enabled',
 *    'class' => '\kartik\grid\BooleanColumn',
 * ],
 * ```
 *
 * Примечания
 * ----------
 * - изменение активности происходит путем установки флага is_disabled.
 * - для удобства добавлено виртуальное свойство is_enabled, оно не хранится в БД
 *
 * @property bool $is_enabled
 * @property bool $is_disabled
 */
trait Disabled
{
  private $_isDisabled = 'is_disabled';

  public function isDisabled()
  {
    return !!$this->getAttribute($this->_isDisabled);
  }

  public function setDisabled()
  {
    $this->setAttribute($this->_isDisabled, 1);
    return $this;
  }

  public function setEnabled()
  {
    $this->setAttribute($this->_isDisabled, 0);
    return $this;
  }

  public function setIs_enabled($enable)
  {
    if ((string)$enable === "") {
      $this->setAttribute($this->_isDisabled, null);
      return;
    }

    $enable ? $this->setEnabled() : $this->setDisabled();
  }

  public function getIs_enabled()
  {
    $disabled = $this->getAttribute($this->_isDisabled);
    return $disabled === null ? null : ($disabled ? 0 : 1);
  }

  public function getIsDisabledLabel()
  {
    return Yii::_t('commonMsg.main.is_disabled');
  }

  public function getIsEnabledLabel()
  {
    return Yii::_t('commonMsg.main.is_enabled');
  }
}