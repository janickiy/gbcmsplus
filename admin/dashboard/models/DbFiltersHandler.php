<?php

namespace admin\dashboard\models;

use mcms\statistic\models\UserStatSettings;
use yii\helpers\ArrayHelper;

/**
 * Class DbFiltersHandler
 * @package admin\dashboard\models
 */
class DbFiltersHandler extends AbstractFiltersHandler
{
    /**
     * @var UserStatSettings
     */
    private $model;

    public function init()
    {
        $this->model = UserStatSettings::findOne($this->userId);
        if (!$this->model) {
            $this->model = new UserStatSettings(['user_id' => $this->userId]);
        }
    }

    /**
     * @inheritdoc
     */
    public function getValue($name, $default = null)
    {
        $filters = $this->getFilters();

        return ArrayHelper::getValue($filters, $name, $default);
    }

    /**
     * @inheritdoc
     */
    public function add($name, $value, $expire = 0)
    {
        $filters = $this->getFilters();

        // чтобы не сохранять по несколько раз
        if (isset($filters[$name]) && $filters[$name] == $value) {
            return;
        }

        $filters[$name] = $value;

        $this->model->dashboard_filters = json_encode($filters);
        $this->model->save();
    }

    /**
     * @inheritdoc
     */
    public function remove($name)
    {
        // если и так нету то не сохраняем по несколько раз
        if (!isset($this->_filters[$name])) {
            return;
        }
        unset($this->_filters[$name]);

        $this->model->dashboard_filters = json_encode($this->_filters);
        $this->model->save();
    }

    private $_filters = null;

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        if ($this->_filters === null) {
            $this->_filters = json_decode($this->model->dashboard_filters, true);
        }
        return $this->_filters;
    }
}