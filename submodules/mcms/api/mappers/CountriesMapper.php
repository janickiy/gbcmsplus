<?php

namespace mcms\api\mappers;

use mcms\api\components\BaseMapper;
use mcms\api\models\Country;
use yii\db\ActiveQuery;

/**
 * Class PartnersMapper
 */
class CountriesMapper extends BaseMapper
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
        'operators' => 'operators'
    ];

    /**
     * @inheritdoc
     */
    public static $availableCustomFields = ['totalRevenue', 'cpaRevenue', 'revshareRevenue', 'otpRevenue'];

    /**
     * @var string
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
            'code' => ['like', $alias . '.code', $this->search],
            'currency' => ['like', $alias . '.currency', $this->search],
        ];

        $conditions = array_intersect_key($conditions, array_flip($this->searchFields));

        return $conditions;
    }

    /**
     * @return ActiveQuery
     */
    public function getRawQuery()
    {
        return Country::find();
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

    /**
     * @inheritdoc
     */
    protected function addSearchJoins($query)
    {
        foreach ($this->relatedMappers as $mapperKey => $relatedMapper) {
            if ($mapperKey === OperatorsMapper::getName()) {
                $this->joinOperators($query);
            }
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
        $query->leftJoin([OperatorsMapper::getName() => 'operators'], OperatorsMapper::getName() . '.country_id = ' . static::getName() . '.id');
        $this->isOperatorsJoined = true;
    }
}
