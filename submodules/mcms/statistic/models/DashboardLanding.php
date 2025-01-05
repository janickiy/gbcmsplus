<?php

namespace mcms\statistic\models;

use mcms\statistic\components\DashboardData;
use yii\base\Model;
use yii\db\Expression;
use yii\db\Query;

/**
 *
 * @property integer $landing_id
 * @property integer $count_hits
 * @property integer $count_tb
 * @property integer $count_ons
 * @property integer $count_onetime
 *
 * @property integer $clicks
 * @property float $ratio
 */
class DashboardLanding extends Model
{
  public $landing_id;
  public $count_hits;
  public $count_tb;
  public $count_ons;
  public $count_onetime;
  public $name;

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'dashboard_landings';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['landing_id', 'count_hits', 'count_tb', 'count_ons', 'count_onetime'], 'integer'],
    ];
  }

  /**
   * @param string $startDate начальная дата выборки
   * @param string $endDate конечная дата выборки
   * @param array $countries страны
   * @param array $operators операторы
   * @param array $users партнеры
   * @return array
   */
  public static function findAll($startDate, $endDate, $countries = [], $operators = [], $users = [])
  {
    return (new Query())->select([
      'landing_id',
      'name',
      new Expression('SUM(count_hits) as count_hits'),
      new Expression('SUM(count_tb) as count_tb'),
      new Expression('SUM(count_ons_revshare) + SUM(count_ons_cpa) as count_ons'),
      new Expression('SUM(count_onetime) as count_onetime')
    ])
      ->from(self::tableName())
      ->leftJoin('landings', 'landing_id = landings.id')
      ->andWhere(['between', 'date', $startDate, $endDate])
      ->andFilterWhere(['country_id' => $countries])
      ->andFilterWhere(['operator_id' => $operators])
      ->andFilterWhere(['user_id' => $users])
      ->groupBy(['landing_id'])->all();
  }

  /**
   * @return int
   */
  public function getClicks()
  {
    return $this->count_hits;
  }

  /**
   * @return float
   */
  public function getRatio()
  {
    $denominator = $this->count_ons + $this->count_onetime;
    $ratio = $denominator ? ($this->count_hits - $this->count_tb) / $denominator : 0;

    return round($ratio, 2);
  }
}
