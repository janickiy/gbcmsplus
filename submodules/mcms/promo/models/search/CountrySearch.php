<?php

namespace mcms\promo\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\promo\models\Country;

/**
 * CountrySearch represents the model behind the search form about `mcms\promo\models\Country`.
 */
class CountrySearch extends Country
{
  const SCENARIO_STAT_FILTERS = 'stat_filters';
  const SCENARIO_ADMIN = 'admin';
  public $onlyWithLandings;
  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['status', 'created_at', 'updated_at'], 'integer'],
      ['id', 'integer', 'except' => self::SCENARIO_STAT_FILTERS],
      [['id'], 'each', 'rule' => ['integer'], 'on' => self::SCENARIO_STAT_FILTERS],
      [['name', 'code', 'currency', 'local_currency', 'onlyWithLandings'], 'safe'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return array_merge(Model::scenarios(), [
      self::SCENARIO_STAT_FILTERS => ['id',  'name'],
      self::SCENARIO_ADMIN => ['id',  'name', 'code', 'status', 'currency', 'local_currency'],
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

    $query = Country::find();

    $query->with(['operator', 'operator.landing']);

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

    if ($this->onlyWithLandings) {
      $query
        ->joinWith('operator.activeLandings')
        ->groupBy(Country::tableName() . '.' . 'id')
        ->orderBy(Country::tableName() . '.' . 'id');
    }

    // grid filtering conditions
    $query->andFilterWhere([
      'id' => $this->id,
      Country::tableName() . '.' . 'status' => $this->status,
    ]);

    $query->andFilterWhere(['like', 'name', $this->name])
      ->andFilterWhere(['like', 'code', $this->code])
      ->andFilterWhere(['currency' => $this->currency])
      ->andFilterWhere(['local_currency' => $this->local_currency]);

    return $dataProvider;
  }
}
