<?php

namespace mcms\api\mappers;

use mcms\api\components\BaseMapper;
use mcms\api\models\OfferCategory;
use yii\db\ActiveQuery;

/**
 * Class PartnersMapper
 */
class OfferCategoriesMapper extends BaseMapper
{
    /**
     * @inheritdoc
     */
    public static $availableFields = [
        'id' => 'id',
        'name' => 'name',
        'code' => 'code',
        'status' => 'status',
    ];

    /**
     * @inheritdoc
     */
    public static $availableRelatedFields = [
        'landings' => 'landings',
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
        ];

        $conditions = array_intersect_key($conditions, array_flip($this->searchFields));

        return $conditions;
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

        $this->applyForceIdsFilter($query);
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
        $query->leftJoin([LandingsMapper::getName() => 'landings'], LandingsMapper::getName() . '.offer_category_id = ' . static::getName() . '.id');
        $query->leftJoin(['lo' => 'landing_operators'], 'lo.landing_id = ' . LandingsMapper::getName() . '.id');
        $query->leftJoin([OperatorsMapper::getName() => 'operators'], OperatorsMapper::getName() . '.id = lo.operator_id');
        $this->isOperatorsJoined = true;
    }

    /**
     * @inheritdoc
     */
    public function getRawQuery()
    {
        return OfferCategory::find()
            ->alias(static::getName());
    }

    /**
     * @inheritdoc
     */
    protected function addSearchJoins($query)
    {
        foreach ($this->relatedMappers as $mapperKey => $relatedMapper) {
            if ($mapperKey === LandingsMapper::getName()) {
                $this->joinOperators($query);
            }
        }
    }
}
