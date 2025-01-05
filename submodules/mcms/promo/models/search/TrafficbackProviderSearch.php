<?php

namespace mcms\promo\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\promo\models\TrafficbackProvider;

/**
 * TraffickbackProviderSearch represents the model behind the search form about `mcms\promo\models\Provider`.
 */
class TrafficbackProviderSearch extends TrafficbackProvider
{
  public $redirectToName;
  const SCENARIO_ADMIN = 'admin';

  public function rules()
  {
    return [
      [['id', 'status', 'category_id', 'created_by', 'created_at', 'updated_at'], 'integer'],
      [['name', 'url'], 'safe'],
    ];
  }

  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return array_merge(Model::scenarios(), [
      self::SCENARIO_ADMIN => ['id', 'name', 'url', 'status', 'category_id'],
    ]);
  }

  public function search($params)
  {
    if ($this->scenario !== self::SCENARIO_ADMIN) {
      $this->status = self::STATUS_ACTIVE;
    }

    $query = TrafficbackProvider::find();

    $query->with(['category']);

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => [
          'id' => SORT_DESC,
        ]
      ],
    ]);

    $this->load($params);
    if (!$this->validate()) return $dataProvider;

    $query->andFilterWhere([
      self::tableName() . '.' . 'id' => $this->id,
      self::tableName() . '.' . 'status' => $this->status,
      self::tableName() . '.' . 'category_id' => $this->category_id,
      self::tableName() . '.' . 'created_by' => $this->created_by,
    ]);

    $query->andFilterWhere(['like', self::tableName() . '.' . 'name', $this->name])
      ->andFilterWhere(['like', self::tableName() . '.' . 'url', $this->url]);

    return $dataProvider;
  }
}
