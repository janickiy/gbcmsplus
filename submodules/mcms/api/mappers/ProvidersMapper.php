<?php

namespace mcms\api\mappers;

use mcms\api\components\BaseMapper;
use mcms\api\models\Provider;
use yii\db\ActiveQuery;

/**
 * Class ProvidersMapper
 */
class ProvidersMapper extends BaseMapper
{
    /**
     * @inheritdoc
     */
    public static $availableFields = [
        'id' => 'id',
        'name' => 'name',
        'code' => 'code',
        'url' => 'url',
        'status' => 'status',
    ];

    /**
     * @inheritdoc
     */
    public static $availableCustomFields = ['totalRevenue', 'cpaRevenue', 'revshareRevenue', 'otpRevenue'];

    /**
     * @var string
     */
    public $defaultField = 'name';

    /**
     * @inheritdoc
     */
    public function getSearchConditions($alias)
    {
        $conditions = [
            'id' => ['like', $alias . '.id', $this->search],
            'name' => ['like', $alias . '.name', $this->search],
            'code' => ['like', $alias . '.code', $this->search],
            'url' => ['like', $alias . '.url', $this->search],
        ];

        $conditions = array_intersect_key($conditions, array_flip($this->searchFields));

        return $conditions;
    }

    /**
     * @return ActiveQuery
     */
    public function getRawQuery()
    {
        return Provider::find();
    }

    /**
     * @inheritdoc
     */
    public function applyFilters(ActiveQuery $query)
    {
        $this->applyForceIdsFilter($query);
    }

    /**
     * @inheritdoc
     */
    public function buildOrderBy(ActiveQuery $query)
    {
        parent::buildOrderBy($query);
        $query->addOrderBy([static::getName() . '.name' => SORT_ASC]);
    }
}
