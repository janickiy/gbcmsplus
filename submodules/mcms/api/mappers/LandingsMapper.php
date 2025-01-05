<?php

namespace mcms\api\mappers;

use mcms\api\components\BaseMapper;
use mcms\api\models\Landing;
use yii\db\ActiveQuery;

/**
 * Class LandingsMapper
 */
class LandingsMapper extends BaseMapper
{
    /**
     * @inheritdoc
     */
    public static $availableFields = [
        'id' => 'id',
        'name' => 'name',
    ];

    /**
     * @inheritdoc
     */
    public static $availableRelatedFields = [
        'operators' => 'operators',
    ];

    /**
     * @inheritdoc
     */
    public static $availableCustomFields = ['totalRevenue', 'cpaRevenue', 'revshareRevenue', 'otpRevenue'];

    /**
     * @inheritdoc
     */
    public $defaultField = 'name';

    private $isOperatorsJoined = false;

    /**
     * @inheritdoc
     */
    public function getSearchConditions($alias)
    {
        $conditions = [
            'id' => ['like', $alias . '.id', $this->search],
            'name' => ['like', $alias . '.name', $this->search],
        ];

        $conditions = array_intersect_key($conditions, array_flip($this->searchFields));

        return $conditions;
    }

    /**
     * @inheritdoc
     */
    public function getRawQuery()
    {
        return Landing::find();
    }

    /**
     * @inheritdoc
     */
    public function applyFilters(ActiveQuery $query)
    {
        if (!$this->isEmptyFilterValue('operators')) {
            $this->joinOperators($query);
            $query->andFilterWhere([OperatorsMapper::getName() . '.id' => $this->filters['operators']]);
        }

        if (!$this->isEmptyFilterValue('countries')) {
            $this->joinOperators($query);
            $query->andFilterWhere([OperatorsMapper::getName() . '.country_id' => $this->filters['countries']]);
        }
    }

    /**
     * @param ActiveQuery $query
     */
    private function joinOperators(ActiveQuery $query)
    {
        if ($this->isOperatorsJoined) {
            return;
        }
        $query->selectOption = 'DISTINCT';
        $query->leftJoin(['lo' => 'landing_operators'], 'lo.landing_id = ' . static::getName() . '.id');
        $query->leftJoin([OperatorsMapper::getName() => 'operators'], OperatorsMapper::getName() . '.id = lo.operator_id');
        $this->isOperatorsJoined = true;
    }
}
