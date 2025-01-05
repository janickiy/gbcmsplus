<?php

namespace mcms\api\components;

use yii\base\Object;

/**
 * Class MapperDataParser
 * @package mcms\api\components
 */
class MapperDataParser extends Objectl
{
  /** Если не передали лимит, то будет 10 */
  const DEFAULT_LIMIT = 10;

  /**
   * @var MapperData
   */
  public $data;

  /**
   * QueryParser constructor.
   * @param MapperData $data
   * @param array $config
   */
  public function __construct(MapperData $data, array $config = [])
  {
    $this->data = $data;

    parent::__construct($config);
  }

  /**
   * @param array $default
   * @return array
   */
  public function getFields($default = [])
  {
    return $this->data->fields ?: $default;
  }

  /**
   * @param array $default
   * @return array
   */
  public function getCustomFields($default = [])
  {
    return $this->data->customFields ?: $default;
  }

  /**
   * @param array $default
   * @return array
   */
  public function getSearchFields($default = [])
  {
    return $this->data->searchFields ?: $default;
  }

  /**
   * @param string $default
   * @return string
   */
  public function getSearchString($default = '')
  {
    return $this->data->searchString ?: $default;
  }

  /**
   * @param array $default
   * @return array
   */
  public function getOrderFields($default = [])
  {
    return $this->data->orderFields ?: $default;
  }

  /**
   * @param int $default
   * @return int
   */
  public function getLimit($default = self::DEFAULT_LIMIT)
  {
    return (int)$this->data->limit ?: $default;
  }

  /**
   * @param int $default
   * @return int
   */
  public function getOffset($default = 0)
  {
    return $this->data->offset ?: $default;
  }

  /**
   * @param int $default
   * @return int
   */
  public function getDepth($default = 0)
  {
    return $this->data->depth ?: $default;
  }

  /**
   * Массив фильтров вида:
   * [
   *  'id' => 2,
   *  '__relatedFilters' => [
   *    'landings' => [
   *      'id' => 2,
   *      '__relatedFilters' => [
   *        'operators' => [
   *          '__relatedFilters' => [
   *            'countries' => [
   *              'id' => 10
   *            ]
   *          ]
   *        ]
   *      ]
   *     ]
   *   ]
   * ]
   * @param array $default
   * @return array
   */
  public function getFilters($default = [])
  {
    return $this->data->filters ?: $default;
  }
}
