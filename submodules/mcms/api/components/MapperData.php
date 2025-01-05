<?php

namespace mcms\api\components;

use yii\base\Object;

/**
 * Class MapperData
 * @package mcms\api\components
 */
class MapperData extends Object
{
    /** @var array */
    public $fields;
    /** @var array */
    public $customFields;
    /** @var array */
    public $searchFields;
    /**
     * @var array
     * Пример: ['totalRevenue' => SORT_DESC, 'sources' => ['totalRevenue' => SORT_DESC]]
     */
    public $orderFields;
    /** @var string */
    public $searchString;
    /** @var int */
    public $limit;
    /** @var int */
    public $offset;
    /** @var int */
    public $depth;
    /** @var array */
    public $filters;
}
