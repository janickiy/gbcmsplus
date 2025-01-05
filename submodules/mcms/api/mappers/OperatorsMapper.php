<?php

namespace mcms\api\mappers;

use mcms\api\components\BaseMapper;
use mcms\api\models\Operator;
use yii\db\ActiveQuery;

/**
 * Class OperatorsMapper
 */
class OperatorsMapper extends BaseMapper
{
    /**
     * @inheritdoc
     */
    public static $availableFields = [
        'id' => 'id',
        'name' => 'name',
        'code' => 'code',
        'currency' => 'currency',
    ];

    /**
     * @inheritdoc
     */
    public static $availableRelatedFields = [
        'countries' => 'country'
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
        ];

        $conditions = array_intersect_key($conditions, array_flip($this->searchFields));

        return $conditions;
    }

    /**
     * @return ActiveQuery
     */
    public function getRawQuery()
    {
        return Operator::find();
    }
}
