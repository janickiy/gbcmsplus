<?php

namespace admin\migrations\dbfix;
interface SettingsInterface extends \Serializable
{
    /**
     * Получает название настройки
     * @return StringObject
     */
    public function getName();

    /**
     * Сеттит название настройки
     * @param StringObject $name
     * @return $this
     */
    public function setName($name);

    /**
     * Возвращает тип настройки
     * @return StringObject
     */
    public function getType();

    /**
     * Возвращает значение настройки
     * @return mixed
     */
    public function getValue();

    /**
     * Сеттит значение настройки
     * @param mixed $value
     * @return $this
     */
    public function setValue($value);

    /**
     * Возвращает ключ конфига
     * @return StringObject
     */
    public function getKey();

    /**
     * Сеттит ключ конфига
     * @param StringObject $key
     * @return $this
     */
    public function setKey($key);

    /**
     * Возвращает валидатор для настройки
     * @return mixed
     */
    public function getValidator();

    /**
     * @return array
     */
    public function getFormAttributes();

    /**
     * @return array
     */
    public function getBehaviors();

    public function beforeValue(&$value);
}
