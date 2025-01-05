<?php

namespace mcms\promo\models\search;

use mcms\common\helpers\ArrayHelper;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use mcms\promo\models\Country;
use mcms\promo\models\Operator;

/**
 * OperatorSearch represents the model behind the search form about `mcms\promo\models\Operator`.
 */
class OperatorSearch extends Operator
{
  const SCENARIO_ADMIN = 'admin';
  public $queryName;
  public $onlyActiveCountries;
  public $countriesIds;
  public $orderByCountry = false;

  const MIN_LENGTH_SEARCH_FROM_BEGINING = 3;
  const SCENARIO_STAT_FILTERS = 'stat_filters';

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['country_id'], 'integer', 'except' => self::SCENARIO_STAT_FILTERS],
      [['country_id', 'id'], 'each', 'rule' => ['integer'], 'on' => self::SCENARIO_STAT_FILTERS],
      [['status', 'created_by', 'created_at', 'updated_at', 'is_3g', 'show_service_url','is_geo_default'], 'integer'],
      [['id'], 'integer', 'except' => self::SCENARIO_STAT_FILTERS],
      [['name', 'is_3g', 'queryName', 'onlyActiveCountries', 'countriesIds'], 'safe'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return array_merge(Model::scenarios(), [
      self::SCENARIO_STAT_FILTERS => ['country_id', 'id'],
      self::SCENARIO_ADMIN => ['id',  'name', 'is_3g', 'show_service_url', 'country_id', 'status','is_geo_default'],
    ]);
  }

  /**
   * Creates data provider instance with search query applied
   *
   * @param array $params
   *
   * @return ActiveDataProvider
   */
  public function search($params)
  {
    if (!in_array($this->scenario, [self::SCENARIO_ADMIN, self::SCENARIO_STAT_FILTERS])) {
      $this->status = self::STATUS_ACTIVE;
    }

    $query = Operator::find();

    $query->with(['activeLandings']);
    $query->joinWith(['country']);

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => [
          'id' => SORT_DESC,
        ]
      ],
    ]);

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      // $query->where('0=1');
      return $dataProvider;
    }

    // grid filtering conditions
    $query->andFilterWhere([
      self::tableName() . '.id' => $this->id,
      self::tableName() . '.country_id' => $this->country_id,
      self::tableName() . '.status' => $this->status,
      self::tableName() . '.is_3g' => $this->is_3g,
      self::tableName() . '.is_geo_default' => $this->is_geo_default,
      self::tableName() . '.show_service_url' => $this->show_service_url,
      self::tableName() . '.created_by' => $this->created_by,
      self::tableName() . '.created_at' => $this->created_at,
      self::tableName() . '.updated_at' => $this->updated_at,
    ]);


    $query->andFilterWhere(['like', self::tableName() . '.name', $this->name]);

    if ($this->onlyActiveCountries) {
      $query->andWhere([Country::tableName() . '.status' => Country::STATUS_ACTIVE]);
    }

    $query->andFilterWhere(['country_id' => $this->countriesIds]);

    $query->andFilterWhere(['like', self::tableName() . '.name', $this->name]);

    if ($this->queryName) {
      $query
        ->andWhere(['!=', self::tableName() . '.' . 'id' ,  $this->queryName])
        ->andWhere([
          'or',
          mb_strlen($this->queryName) > self::MIN_LENGTH_SEARCH_FROM_BEGINING
            ? ['like', self::tableName() . '.' . 'name' ,  $this->queryName]
            : ['like', self::tableName() . '.' . 'name' ,  $this->queryName . '%', false]
          ,
          ['like', self::tableName() . '.' . 'id' ,  $this->queryName],
        ]);
    }

    if ($this->orderByCountry) {
      $query->select(self::tableName(). '.*, ' . Country::tableName().'.name as country');
      $query->orderBy([new Expression('country ASC, ' . self::tableName(). '.name ASC')]);
    }

    return $dataProvider;
  }
}
