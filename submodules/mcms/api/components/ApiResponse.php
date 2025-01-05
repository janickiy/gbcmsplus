<?php

namespace mcms\api\components;


use yii\base\Object;

/**
 * Class ApiResponse
 * @package mcms\api\components
 */
class ApiResponse extends Object
{
    /**
     * @var bool
     */
    public $success = true;

    /**
     * @var array
     */
    public $data = [];

    /**
     * @var string
     */
    public $total_count;

    /**
     * @var string
     */
    public $page_count;

    /**
     * @var string
     */
    public $current_page;

    /**
     * @var int
     */
    public $per_page;

    public $next_page = '';

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this);
    }
}