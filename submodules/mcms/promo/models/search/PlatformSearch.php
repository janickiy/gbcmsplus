<?php

namespace mcms\promo\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\promo\models\Platform;

/**
 * PlatformSearch represents the model behind the search form about `mcms\promo\models\Platform`.
 */
class PlatformSearch extends Platform
{
  const SCENARIO_STAT_FILTERS = 'stat_filters';
  const SCENARIO_ADMIN = 'admin';
  public $createdFrom;
  public $createdTo;

  public function rules()
  {
    return [
      [['created_at', 'updated_at', 'status'], 'integer'],
      [['id'], 'integer', 'except' => self::SCENARIO_STAT_FILTERS],
      [['id'], 'each', 'rule' => ['integer'], 'on' => self::SCENARIO_STAT_FILTERS],
      [['name', 'match_string', 'status', 'createdFrom', 'createdTo'], 'safe'],
    ];
  }

  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return array_merge(Model::scenarios(), [
      self::SCENARIO_STAT_FILTERS => ['id'],
      self::SCENARIO_ADMIN => ['id', 'name', 'match_string', 'status', 'created_at', 'createdFrom', 'createdTo'],
    ]);
  }

  public function search($params)
  {

    if ($this->scenario !== self::SCENARIO_ADMIN) {
      $this->status = self::STATUS_ACTIVE;
    }

    $query = Platform::find();

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
      'updated_at' => $this->updated_at,
      'status' => $this->status,
    ]);

    $query->andFilterWhere(['like', 'name', $this->name])
      ->andFilterWhere(['like', 'match_string', $this->match_string]);

    if ($this->createdFrom) {
      $query->andFilterWhere(['>=', 'created_at', strtotime($this->createdFrom . ' 00:00:00')]);
    }
    if ($this->createdTo) {
      $query->andFilterWhere(['<=', 'created_at', strtotime($this->createdTo . ' 23:59:59')]);
    }

    return $dataProvider;
  }
}