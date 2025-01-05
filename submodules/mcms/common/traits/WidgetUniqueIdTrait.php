<?php

namespace mcms\common\traits;

use mcms\common\helpers\Html;

/**
 * Генерирует уникальный ID для виджета.
 * ID записывается в options.
 * Решение создано что бы идентификаторы блоков вида #w0, #w1 не конфликтовали при подгрузке через ajax,
 * так как стандратное решение генерирует идентификатор в рамках выполнения скрипта и не знает о том,
 * какие идентификаторы уже есть на странице использующей ajax.
 */
trait WidgetUniqueIdTrait
{
  private $_id;

  public function init()
  {
    if (empty($this->options['id'])) {
      $this->options['id'] = $this->getId();
    }

    parent::init();
  }

  /**
   * Returns the ID of the widget.
   * @param boolean $autoGenerate whether to generate an ID if it is not set previously
   * @return string ID of the widget.
   */
  public function getId($autoGenerate = true)
  {
    if ($autoGenerate && $this->_id === null) {
      $this->_id = Html::getUniqueId();
    }

    return $this->_id;
  }

  /**
   * Sets the ID of the widget.
   * @param string $value id of the widget.
   */
  public function setId($value)
  {
    $this->_id = $value;
  }
}