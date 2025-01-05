<?php

namespace mcms\api\mappers;

use mcms\api\components\BaseMapper;
use mcms\api\models\Platform;
use yii\db\ActiveQuery;

/**
 * Class PlatformsMapper
 */
class PlatformsMapper extends BaseMapper
{
    /**
   * @inheritdoc
   */
  public static $availableFields = [
    'id' => 'id',
    'name' => 'name',
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
    ];

    $conditions = array_intersect_key($conditions, array_flip($this->searchFields));

    return $conditions;
  }

  /**
   * @inheritdoc
   */
  public function applyFilters(ActiveQuery $query)
  {
    $this->applyForceIdsFilter($query);
  }

  /**
   * @return ActiveQuery
   */
  public function getRawQuery()
  {
    return Platform::find()->andWhere(['status' => Platform::STATUS_ACTIVE]);
  }
}
