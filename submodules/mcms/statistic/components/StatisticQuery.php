<?php

namespace mcms\statistic\components;

use Yii;
use yii\db\Query;

/**
 * Class StatisticQuery
 * @package mcms\statistic\components
 *
 * @property bool $hitParamsJoined
 */
class StatisticQuery extends Query
{
  /**
   * @var
   */
  protected $id;
  /**
   * @var bool
   */
  protected $_hitParamsJoined = false;

  /**
   * @return bool
   */
  public function getHitParamsJoined() :bool
  {
    return $this->_hitParamsJoined;
  }

  /**
   * @param bool $value
   */
  public function setHitParamsJoined(bool $value)
  {
    $this->_hitParamsJoined = $value;
  }

  /**
   * @param $value
   * @return $this
   */
  public function setId($value)
  {
    $this->id = $value;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @param array $condition
   * @return $this
   */
  public function andFilterHaving(array $condition)
  {
    $condition = $this->filterCondition($condition);
    if ($condition !== []) {
      $this->andHaving($condition);
    }
    return $this;
  }
}
