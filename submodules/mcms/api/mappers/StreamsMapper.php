<?php

namespace mcms\api\mappers;

use mcms\api\components\BaseMapper;
use mcms\api\models\Stream;
use yii\db\ActiveQuery;

/**
 * Class StreamsMapper
 */
class StreamsMapper extends BaseMapper
{
    /**
     * @inheritdoc
     */
    public static $availableFields = [
        'id' => 'id',
        'name' => 'name',
        'status' => 'status',
        'user_id' => 'user_id',
    ];

    /**
     * @inheritdoc
     */
    public static $availableRelatedFields = [
        'users' => 'users',
        'sources' => 'sources',
    ];

    /**
     * @inheritdoc
     */
    public static $availableCustomFields = ['totalRevenue', 'cpaRevenue', 'revshareRevenue', 'otpRevenue'];

    /**
     * @inheritdoc
     */
    public $defaultField = 'name';

    private $isSourcesJoined = false;

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
        return Stream::find();
    }

    /**
     * @inheritdoc
     */
    public function buildOrderBy(ActiveQuery $query)
    {
        parent::buildOrderBy($query);
        $query->addOrderBy([static::getName() . '.id' => SORT_ASC]);
    }

    /**
     * @inheritdoc
     */
    public function applyFilters(ActiveQuery $query)
    {
        if (!$this->isEmptyFilterValue('users')) {
            $query->andFilterWhere([static::getName() . '.user_id' => $this->filters['users']]);
        }

        if (!$this->isEmptyFilterValue('sources')) {
            $this->joinSources($query);
            $query->andFilterWhere([SourcesMapper::getName() . '.id' => $this->filters['sources']]);
        }

        $this->applyForceIdsFilter($query);
    }

    /**
     * @param ActiveQuery $query
     */
    private function joinSources(ActiveQuery $query)
    {
        if ($this->isSourcesJoined) {
            return;
        }
        $query->selectOption = 'DISTINCT';
        $query->leftJoin([SourcesMapper::getName() => 'sources'], SourcesMapper::getName() . '.stream_id = ' . static::getName() . '.id');
        $this->isSourcesJoined = true;
    }
}
