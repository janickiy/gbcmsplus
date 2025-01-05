<?php

namespace mcms\api\mappers;

use mcms\api\components\BaseMapper;
use mcms\api\models\Source;
use yii\db\ActiveQuery;

/**
 * Class SourcesMapper
 */
class SourcesMapper extends BaseMapper
{
    /**
     * @inheritdoc
     */
    public static $availableFields = [
        'id' => 'id',
        'hash' => 'hash',
        'url' => 'url',
        'name' => 'name',
        'status' => 'status',
        'stream_id' => 'stream_id',
    ];

    /**
     * @inheritdoc
     */
    public static $availableRelatedFields = [
        'partner' => 'partners',
        'stream' => 'streams',
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
            'hash' => ['like', $alias . '.hash', $this->search],
            'url' => ['like', $alias . '.url', $this->search],
            'name' => ['like', $alias . '.name', $this->search],
            'status' => ['like', $alias . '.status', $this->search],
        ];

        $conditions = array_intersect_key($conditions, array_flip($this->searchFields));

        return $conditions;
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
        if (!$this->isEmptyFilterValue('streams')) {
            $query->andFilterWhere([static::getName() . '.stream_id' => $this->filters['streams']]);
        }
    }

    /**
     * @inheritdoc
     */
    public function getRawQuery()
    {
        return Source::find();
    }
}
