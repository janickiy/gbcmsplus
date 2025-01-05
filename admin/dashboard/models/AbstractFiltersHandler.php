<?php

namespace admin\dashboard\models;

use yii\base\Component;

/**
 * Class AbstractFiltersHandler
 * @package admin\dashboard\models
 */
abstract class AbstractFiltersHandler extends Component
{
    const FILTER_COUNTRIES_NAME = 'countries';
    const FILTER_PERIOD_NAME = 'period';
    const FILTER_FORECAST_NAME = 'forecast';
    const FILTER_CURRENCY_NAME = 'currency';
    const FILTER_PUBLISHER_TYPE_NAME = 'publisher_type';
    const FILTER_COUNTRY_TYPE_NAME = 'country_type';
    const FILTER_DURATION = 24 * 60 * 60;

    public $userId = null;

    /**
     * @param string $name
     * @param null|string $default
     * @return mixed
     */
    public abstract function getValue($name, $default = null);

    /**
     * @return array
     */
    public abstract function getFilters();

    /**
     * @param $name
     * @param $value
     * @param int $expire
     */
    public abstract function add($name, $value, $expire = 0);

    /**
     * @param $name
     * @return mixed
     */
    public abstract function remove($name);
}