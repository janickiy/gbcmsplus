<?php

namespace admin\dashboard\common;

use Yii;
use yii\base\Object;
use yii\web\Response;

/**
 * Class DasboardResponse
 * @package admin\dashboard\common
 *
 * Класс для подготовки ответа в специальный формат для js модуля DashboardRequest
 */
class DasboardResponse extends Object
{
    /**
     * @var array
     */
    protected $widgets;

    /**
     * @var array
     */
    protected $gadgets;

    /**
     * @param string $name
     * @param mixed $data
     */
    public function setWidget(string $name, $data)
    {
        $this->widgets[$name] = [
            'name' => $name,
            'data' => $data,
        ];
    }

    /**
     * @param string $name
     * @param mixed $data
     */
    public function setGadget($name, $data)
    {
        $this->gadgets[$name] = [
            'name' => $name,
            'data' => $data,
        ];
    }

    /**
     * @return array
     */
    public function send()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'widgets' => $this->widgets,
            'gadgets' => $this->gadgets,
        ];
    }
}