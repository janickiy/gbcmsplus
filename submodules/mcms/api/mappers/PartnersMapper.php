<?php

namespace mcms\api\mappers;

use mcms\api\components\BaseMapper;
use mcms\api\models\User;
use yii\db\ActiveQuery;

/**
 * Class PartnersMapper
 */
class PartnersMapper extends BaseMapper
{
    /**
     * @inheritdoc
     */
    public static $availableFields = [
        'id' => 'id',
        'username' => 'username',
        'language' => 'language',
        'email' => 'email',
        'status' => 'status',
    ];

    /**
     * @inheritdoc
     */
    public static $availableRelatedFields = [
        'sources' => 'sources',
        'streams' => 'streams',
    ];

    /**
     * @inheritdoc
     */
    public static $availableCustomFields = ['totalRevenue', 'cpaRevenue', 'revshareRevenue', 'otpRevenue'];

    private $isSourcesJoined = false;
    private $isStreamsJoined = false;

    /**
     * @param string $alias
     * @return array
     */
    public function getSearchConditions($alias)
    {
        $conditions = [
            'id' => ['like', $alias . '.id', $this->search],
            'username' => ['like', $alias . '.username', $this->search],
            'language' => ['like', $alias . '.language', $this->search],
            'email' => ['like', $alias . '.email', $this->search],
            'status' => ['=', $alias . '.status', $this->search],
        ];

        $conditions = array_intersect_key($conditions, array_flip($this->searchFields));

        return $conditions;
    }

    /**
     * @inheritdoc
     */
    public function getRawQuery()
    {
        $query = User::find()
            ->alias(self::getName())
            // todo исправить на просто join
            ->innerJoinWith(['roles' => function ($query) {
                /** @var ActiveQuery $query */
                $query->andOnCondition(['auth_item.name' => 'partner']);
            }]);

        return $query;
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
    protected function addSearchJoins($query)
    {
        foreach ($this->relatedMappers as $mapperKey => $relatedMapper) {
            if ($mapperKey === SourcesMapper::getName()) {
                $this->joinSources($query);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function applyFilters(ActiveQuery $query)
    {
        if (!$this->isEmptyFilterValue('streams')) {
            $this->joinStreams($query);
            $query->andFilterWhere([StreamsMapper::getName() . '.id' => $this->filters['streams']]);
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
        $query->leftJoin([SourcesMapper::getName() => 'sources'], SourcesMapper::getName() . '.user_id = ' . static::getName() . '.id');
        $this->isSourcesJoined = true;
    }

    /**
     * @param ActiveQuery $query
     */
    private function joinStreams(ActiveQuery $query)
    {
        if ($this->isStreamsJoined) {
            return;
        }
        $query->selectOption = 'DISTINCT';
        $query->leftJoin([StreamsMapper::getName() => 'streams'], StreamsMapper::getName() . '.user_id = ' . static::getName() . '.id');
        $this->isStreamsJoined = true;
    }

    /**
     * @inheritdoc
     */
    public static function getStatFilterBy()
    {
        return 'users'; // иначе стата будет пытаться отфильтровать по partners
    }

    /**
     * @inheritdoc
     */
    public static function getStatGroupBy()
    {
        return 'users'; // иначе стата будет пытаться сгруппировать по partners
    }
}
