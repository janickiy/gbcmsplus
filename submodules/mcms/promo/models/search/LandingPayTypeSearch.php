<?php

namespace mcms\promo\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\promo\models\LandingPayType;

/**
 * LandingPayTypeSearch represents the model behind the search form about `mcms\promo\models\LandingPayType`.
 */
class LandingPayTypeSearch extends LandingPayType
{

  const SCENARIO_STAT_FILTERS = 'stat_filters';
  const SCENARIO_ADMIN = 'admin';
  public $createdFrom;
  public $createdTo;

  public function rules()
  {
    return [
      [['created_at', 'updated_at'], 'integer'],
      [['id'], 'integer', 'except' => self::SCENARIO_STAT_FILTERS],
      [['id'], 'each', 'rule' => ['integer'], 'on' => self::SCENARIO_STAT_FILTERS],
      [['name', 'createdFrom', 'createdTo', 'status', 'code'], 'safe'],
    ];
  }

  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return array_merge(Model::scenarios(), [
      self::SCENARIO_STAT_FILTERS => ['id'],
      self::SCENARIO_ADMIN => ['id', 'code', 'name', 'status', 'created_at']
    ]);
  }

  public function search($params)
  {
    $query = LandingPayType::find();

    if (!in_array($this->scenario, [self::SCENARIO_ADMIN, self::SCENARIO_STAT_FILTERS])) {
      $this->status = self::STATUS_ACTIVE;
    }

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
      return $dataProvider;
    }

    $query->andFilterWhere([
      'id' => $this->id,
      'created_at' => $this->created_at,
      'status' => $this->status,
      'updated_at' => $this->updated_at,
    ]);

    $query->andFilterWhere(['like', 'name', $this->name]);
    $query->andFilterWhere(['like', 'code', $this->code]);

    if ($this->createdFrom) {
      $query->andFilterWhere(['>=', 'created_at', strtotime($this->createdFrom)]);
    }
    if ($this->createdTo) {
      $query->andFilterWhere(['<=', 'created_at', strtotime($this->createdTo)]);
    }

    return $dataProvider;
  }
}