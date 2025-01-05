<?php

namespace admin\migrations\dbfix;

use Yii;
use yii\base\DynamicModel;
use yii\helpers\BaseInflector;

class Repository implements \Serializable, \Iterator
{
    /** @var  SettingsInterface[] */
    private $_data = [];
    private $moduleId;

    public function set(SettingsInterface $settings)
    {
        $this->_data[$settings->getKey()] = $settings;
        return $this;
    }

    public function setModuleId($id)
    {
        $this->moduleId = $id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_data);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->_data[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        $this->_data[$offset]->setValue($value);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return serialize($this->_data);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        $this->_data = unserialize($serialized);
    }

    public function mergeValues(Repository $settings)
    {
        foreach ($settings as $key => $setting) {
            if ($this->offsetExists($key)) $this->_data[$key]->setValue($setting->getValue());
        }
    }

    public function getFormAttributes()
    {
        $attributes = [];
        foreach ($this->_data as $setting) {
            if (!$this->canEditSetting($this->moduleId, $setting)) continue;
            $attributes[$setting->getKey()] = $setting->getFormAttributes();
        }

        return $attributes;
    }

    public function getValues()
    {
        $attributes = [];
        foreach ($this->_data as $setting) {
            $attributes[$setting->getKey()] = $setting->getValue();
        }

        return $attributes;
    }

    public function getValueByKey($key, $default = NULL)
    {
        if ($settings = $this->offsetGet($key)) {
            return $settings->getValue();
        }

        return $default;
    }

    public function current()
    {
        return current($this->_data);
    }

    public function next()
    {
        next($this->_data);
    }

    public function key()
    {
        return key($this->_data);
    }

    public function valid()
    {
        $key = $this->key();
        return $key !== NULL && $key !== FALSE;
    }

    public function rewind()
    {
        reset($this->_data);
    }

    public function import(DynamicModel $model)
    {
        foreach ($model->getAttributes() as $key => $value) {
            $this->_data[$key]->beforeValue($value);
            $this->offsetSet($key, $value);
        }
    }

    public function hasSettings()
    {
        return count($this->_data);
    }

    public function canEditSetting($moduleId, SettingsAbstract $settings)
    {
        $permissions = ['EditModuleSettings' . BaseInflector::camelize($moduleId)];
        if (($settingsPermissions = $settings->getPermissions()) && count($settingsPermissions)) {
            $permissions = array_merge($permissions, $settingsPermissions);
        }

        return $this->canEdit($permissions);
    }

    public function canEdit(array $permissions)
    {
        foreach ($permissions as $permission) {
            if (!Yii::$app->user->can($permission)) return false;
        }
        return true;
    }

}
