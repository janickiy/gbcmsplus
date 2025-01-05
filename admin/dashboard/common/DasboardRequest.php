<?php

namespace admin\dashboard\common;

use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * Class DasboardRequest
 * @package admin\dashboard\common
 *
 * Класс для обработки данных, полученных от дашборда
 */
class DasboardRequest extends Object
{
    const PREFIX = '';

    /**
     * @var array список фильтров для всех элементов дашборда
     */
    protected $filters = [];

    /**
     * @var array список полученных виджетов
     */
    protected $widgets = [];

    /**
     * @var array список полученных гаджетов
     */
    protected $gadgets = [];

    /**
     * DasboardRequest constructor.
     * @param array $requestParams
     * @param array $config
     */
    public function __construct(array $requestParams, array $config = [])
    {
        $this->configure($requestParams);
        parent::__construct($config);
    }

    /**
     * @param array $params
     */
    protected function configure(array $params)
    {
        foreach ($params as $param => $value) {
            $prop = str_replace(self::PREFIX, '', $param);
            if ($this->canSetProperty($prop)) {
                $this->$prop = $value;
            }
        }
    }

    /**
     * @return bool
     */
    public function hasWidgets()
    {
        return (bool)count($this->widgets);
    }

    /**
     * @return bool
     */
    public function hasGadgets()
    {
        return (bool)count($this->gadgets);
    }

    /**
     * @return array
     */
    public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * @return array
     */
    public function getGadgets()
    {
        return $this->gadgets;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getFilter($name)
    {
        return ArrayHelper::getValue($this->filters, $name);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getWidget($name)
    {
        return ArrayHelper::getValue($this->widgets, $name);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getGadget($name)
    {
        return ArrayHelper::getValue($this->gadgets, $name);
    }
}