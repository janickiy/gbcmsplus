<?php

namespace admin\migrations\dbfix;

use Yii;
use kartik\builder\Form;;
use yii\helpers\ArrayHelper;

class Options extends SettingsAbstract
{
    protected $type = Form::INPUT_RADIO_LIST;
    protected $options;
    protected $closure;

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->getItems($this->options);
    }

    /**
     * @param $key
     * @param $name
     * @return $this
     */
    public function setOption($key, $name)
    {
        $this->options[$key] = $name;
        return $this;
    }

    /**
     * Получить опцию по коду
     * @param $key
     * @return mixed
     */
    public function getOption($key)
    {
        return Yii::_t(ArrayHelper::getValue($this->options, $key));
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    public function setOptionsClojure(\Closure $options)
    {
        $this->closure = $options;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFormAttributes()
    {
        return array_merge(parent::getFormAttributes(), [
            'items' => $this->getItems(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getValidator()
    {
        return [['required']];
    }

    protected function getLabel($label)
    {
        return '<span>' . Yii::_t($label) . '</span>';
    }

    private function getItems()
    {
        if ($this->closure) {
            $clojure = $this->closure;
            $this->options = $clojure();
        }
        return array_map(function ($label) {
            return $this->getLabel($label);
        }, $this->options);
    }
}